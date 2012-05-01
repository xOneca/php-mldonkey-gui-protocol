<?php
/*
 * Copyright (C) 2012 Xabier Oneca <xoneca+php-mldonkey-gui-protocol@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

require( 'mlnet.opcodes.php' );

define( 'MSGLEN_LEN', 4 ); // Bytes for message length
define( 'OPCODE_LEN', 2 ); // Bytes for opcode number

/**
 * Generic class for all GUIprotocol messages.
 *
 * - Size of Content (Header): int32
 * - Opcode:                   int16
 * - Payload:                  variable-size
 */
class CMessage
{
    var $opcode = 0;     ///< Message opcode
    var $socket = null;  ///< Connection socket
    var $raw_msg = null; ///< CRaw_Data object

    function __construct( &$socket )
    {
        $this->socket = $socket;
    }

    /**
     * Read raw message data from stream.
     *
     * @return if something was read or not.
     */
    function read( )
    {
        $msg = new CRaw_Data( fread( $this->socket, MSGLEN_LEN ) );

        if( strlen($msg->data) != MSGLEN_LEN )
            return false;

        $msg_len = $msg->read_raw_int32( );

        if( !$msg_len )
            return false;

        $this->raw_msg = new CRaw_Data( fread( $this->socket, $msg_len ) );
        $this->opcode = $this->raw_msg->read_raw_int16( );

        return strlen( $this->raw_msg->data ) > 0;
    }

    /**
     * Send the message to the server.
     *
     * @return \c true on success.
     */
    function send( )
    {
        if( $this->raw_msg === null )
            $this->raw_msg = new CRaw_Data();

        if( !strlen($this->raw_msg->data) && !$this->build() )
            return false;

        $msg_len = pack( 'V', strlen( $this->raw_msg->data ) + OPCODE_LEN );
        $opcode = pack( 'v', $this->opcode );

        // For consistency with read()
        $this->raw_msg->data = $opcode . $this->raw_msg->data;

        fwrite( $this->socket, $msg_len . $this->raw_msg->data );

        return true;
    }

    /**
     * Parses raw data into class properties.
     *
     * It must be overriden in each subclass and implement needed code for
     * parsing the message.
     *
     * @return \c true on success.
     */
    function expand( )
    {
        return false;
    }

    /**
     * Parses local properties and prepares raw data to send.
     *
     * It must be overriden in each subclass and implement needed code for
     * preparing object data to be ready to send.
     *
     * @return \c true on success
     */
    function build( )
    {
        return false;
    }

    /**
     * Converts this generic class into the appropiate subclass.
     *
     * Reads the message opcode and returns the corresponding subclass with
     * the same message ready to be expand()-ed.
     *
     * @return a \c CMessage subclass.
     */
    function &convert( )
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $class_name = 'COpcode_' . opcode_name( $this->opcode );

        if( class_exists($class_name) )
        {
            // Prepare new object as if it was read from this stream
            $opcode_object = new $class_name( $this->socket );
            $opcode_object->raw_msg = new CRaw_Data( $this->raw_msg->data );
            $opcode_object->opcode = $opcode_object->raw_msg->read_raw_int16( );

            return $opcode_object;
        }

        fprintf(
            STDERR, "Message type %s (%u) is not implemented!\n",
            opcode_name( $this->opcode), $this->opcode
        );

        // We can't convert the message, return original
        return $this;
    }
}

/**
 * Accessing more confortably raw data
 */
class CRaw_Data
{
    var $data = '';     ///< Raw data from/to socket
    var $pointer = 0;   ///< Current position when reading from raw data.

    function __construct( $data = '' )
    {
        $this->data = $data;
        $this->pointer = 0;
    }

    /// @note Important running PHP on a 64-bit system
    function read_raw_int64( )
    {
        // unpack() can not read an int64 (yet), so we read
        // two in32 and join them.
        $read = unpack( 'V2', substr( $this->data, $this->pointer ) );
        $this->pointer += 8;

        return $read[1] | ($read[2] << 32);
    }

    /// @note Important running PHP on a 64-bit system
    function write_raw_int64( $value )
    {
        $this->data .= pack(
            'VV',
            ($value >> 32) & 0xffffffff,
            $value & 0xffffffff
        );
    }

    function read_raw_int32( )
    {
        $read = unpack( 'V', substr( $this->data, $this->pointer ) );
        $this->pointer += 4;

        return $read[1];
    }

    function write_raw_int32( $value )
    {
        $this->data .= pack( 'V', $value );
    }

    function read_raw_int16( )
    {
        $read = unpack( 'v', substr( $this->data, $this->pointer ) );
        $this->pointer += 2;

        return $read[1];
    }

    function write_raw_int16( $value )
    {
        $this->data .= pack( 'v', $value );
    }

    function read_raw_int8( )
    {
        $read = unpack( 'C', substr($this->data, $this->pointer) );
        $this->pointer++;

        return $read[1];
    }

    function write_raw_int8 ( $value )
    {
        $this->data .= pack( 'C', $value );
    }

    function read_raw_bool( )
    {
        return $this->read_raw_int8() ? true : false;
    }

    function write_raw_bool( $value )
    {
        // It is an int8 in essence
        $this->write_raw_int8( $value === true ? 1 : 0 );
    }

    function read_raw_string( )
    {
        $len = $this->read_raw_int16( );

        // Do not know when did they add this to the protocol, but it appears
        // in the code
        if( $len == 0xffff )
             $len = $this->read_raw_int32( );

        $string = substr( $this->data, $this->pointer, $len );
        $this->pointer += $len;

        return $string;
    }

    function write_raw_string( $value )
    {
        $this->write_raw_int16( strlen($value) );

        $this->data .= $value;
    }

    /**
     * Read a float number from the buffer.
     *
     * The number is a string in the stream with the integer part before the
     * dot and the hundredths after the dot. So "7.3" represents 7.03.
     *
     * @return the "decoded" float value.
     */
    function read_raw_float( )
    {
        $num_str = $this->read_raw_string( );
        list( $integer, $hundredths ) = explode( '.', $num_str );

        return intval( $integer ) + intval( $hundredths ) / 100;
    }

    /**
     * Encode a float value and append it to the buffer.
     *
     * @see read_raw_float()
     *
     * @param $value Float value you want to send.
     */
    function write_raw_float( $value )
    {
        $int_part = intval( $value );
        $hundredths =  intval( $value * 100 - $int_part * 100 );

        $string = sprintf( '%u.%u', $int_part, $hundredths );
        $this->write_raw_string( $string );
    }

    function read_raw_address( )
    {
        $type = $this->read_raw_int8( );

        // Address is IP
        if( $type == 0 )
        {
            $ip = $this->read_raw_int32( );
            $geoip = $this->read_raw_int8( );
            $blocked = $this->read_raw_int8( ) ? true : false;

            return array(
                'ip' => $ip,
                'geoip' => $geoip,
                'blocked' => $blocked
            );
        }

        // Address is name
        elseif( $type == 1 )
        {
            $geoip = $this->read_raw_int8( );
            $name = $this->read_raw_string( );
            $blocked = $this->read_raw_int8( ) ? true : false;

            return array(
                'name' => $name,
                'geoip' => $geoip,
                'blocked' => $blocked
            );
        }

        return false;
    }

    /**
     * Read a binary 16-byte hash.
     *
     * @return the binary hash as-is.
     */
    function read_raw_hash( )
    {
        $hash = substr( $this->data, $this->pointer, 16 );
        $this->pointer += 16;

        return $hash;
    }

    /**
     * Write a 16-byte hash.
     *
     * @param $hash the binary hash.
     */
    function write_raw_hash( $hash )
    {
        $this->data .= sprintf( '%16.16s', $hash );
    }

    /**
     * Read a pair of (\c name, \c value) from buffer.
     *
     * \c value can be a number, string or an array.
     *
     * @return an array with the \c name as a key and the \c value as its value.
     *
     * @note the returned array should be merged if there are more tags
     *       decoded to an array.
     */
    function read_raw_tag( )
    {
        $name = $this->read_raw_string( );
        $type = $this->read_raw_int8( );

        switch( $type )
        {
            case 0:
                $value = $this->read_raw_int32( );
                break;

            case 1:
                $value = $this->read_raw_int32( );
                if( $value > 0x7fffffff ) $value = -($value & 0x7fffffff);
                break;

            case 2:
                $value = $this->read_raw_string( );
                break;

            case 3:
                $value = long2ip( $this->read_raw_int32( ) );
                break;

            case 4:
                $value = $this->read_raw_int16( );
                break;

            case 5:
                $value = $this->read_raw_int8( );
                break;

            case 6:
                $value = array(
                    $this->read_raw_int32( ),
                    $this->read_raw_int32( )
                );
                break;

            default:
                $value = null;
        }

        return array( $name => $value );
    }
}

// Classes for messages received from the core
require_once( 'mlnet.class_received.php' );

// Classes for messages sent to the core
require_once( 'mlnet.class_sent.php' );
