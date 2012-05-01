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

/**
 * Protocol definition.
 *
 * Opcode 0 (0x00)
 */
class COpcode_CoreProtocol extends CMessage
{
    var $version = 0;
    var $max_opcode_sent = 0;
    var $max_opcode_accepted = 0;

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->version = $this->raw_msg->read_raw_int32();

        // If version > 25 then the server sends more info
        if( $this->version > 25 )
        {
            $this->max_opcode_sent = $this->raw_msg->read_raw_int32();
            $this->max_opcode_accepted = $this->raw_msg->read_raw_int32();
        }

        return true;
    }
}

/**
 * Options info.
 *
 * Opcode 1 (0x01)
 */
class COpcode_OptionsInfo extends CMessage
{
    var $options = array();

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $str_count = $this->raw_msg->read_raw_int16();

        for( $i = 0; $i < $str_count; $i++ )
        {
            $opt1 = $this->raw_msg->read_raw_string();
            $opt2 = $this->raw_msg->read_raw_string();
            $this->options[$opt1] = $opt2;
        }

        return true;
    }
}

/**
 * A Tree corresponding to a Custom Search with fields to be filled by the user.
 *
 * Opcode 3 (0x03)
 */
class COpcode_DefineSearches extends CMessage
{
    var $queries = array();

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->queries = array();
        $num_queries = $this->raw_msg->read_raw_int16();

        for( $i = 0; $i < $num_queries; $i++ )
        {
            $name = $this->raw_msg->read_raw_string();
            $this->queries[$name] = $this->_read_query();
        }

        return true;
    }

    private function _read_query()
    {
        $query_type = $this->raw_msg->read_raw_int8();

        switch( $query_type )
        {
            case 0:
            case 1:
            case 13:
                $list = array();
                $num_queries = $this->raw_msg->read_raw_int16();
                for( $i = 0; $i < $num_queries; $i++ )
                    $list[] = $this->_read_query();

                return array(
                    'type' => $query_type,
                    'queries' => $list
                );
                break;

            case 2:
                $q1 = $this->_read_query();
                $q2 = $this->_read_query();

                return array(
                    'type' => $query_type,
                    'queries' => array( $q1, $q2 )
                );
                break;

            case 3:
                $module_name = $this->raw_msg->read_raw_string();
                $query = $this->_read_query();

                return array(
                    'type' => $query_type,
                    'module' => $module_name,
                    'query' => $query
                );
                break;

            default:
                $comment = $this->raw_msg->read_raw_string();
                $default = $this->raw_msg->read_raw_string();

                return array(
                    'type' => $query_type,
                    'comment' => $comment,
                    'default' => $default
                );
                break;
        }
    }
}

/**
 * Search result.
 *
 * Opcode 4 (0x04)
 */
class COpcode_ResultInfo extends CMessage
{
    var $result_id = 0;         ///< Result ID.
    var $network_id = 0;        ///< Network ID.
    var $file_names = array();  ///< Array of file names.
    var $file_ids = array();    ///< Array of file IDs.
    var $size = 0;              ///< File size in bytes.
    var $format = '';           ///< File format.
    var $type = '';             ///< File type.
    var $metadata = array();    ///< Metadata.
    var $comment = '';          ///< File comment.
    var $downloaded = false;    ///< Whether the file was previously downloaded.
    var $time = 0;              ///< Timestamp.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->result_id = $this->raw_msg->read_raw_int32();
        $this->network_id = $this->raw_msg->read_raw_int32();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            $this->file_names[] = $this->raw_msg->read_raw_string();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            $this->file_ids[] = $this->raw_msg->read_raw_string();

        $this->size = $this->raw_msg->read_raw_int64();
        $this->format = $this->raw_msg->read_raw_string();
        $this->type = $this->raw_msg->read_raw_string();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            $this->metadata[] = $this->raw_msg->read_raw_tag();

        $this->comment = $this->raw_msg->read_raw_string();
        $this->downloaded = $this->raw_msg->read_raw_int8() ? true : false;
        $this->time = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Binds a search with a result.
 *
 * Opcode 5 (0x05)
 */
class COpcode_SearchResult extends CMessage
{
    var $search_id = 0;
    var $result_id = 0;

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->search_id = $this->raw_msg->read_raw_int32();
        $this->result_id = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * FileUpdateAvailability
 *
 * Opcode 9 (0x09)
 */
class COpcode_FileUpdateAvailability extends CMessage
{
    var $file_num = 0;
    var $client_num = 0;
    var $availability = '';

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->file_num = $this->raw_msg->read_raw_int32();
        $this->client_num = $this->raw_msg->read_raw_int32();
        $this->availability = $this->raw_msg->read_raw_string();

        return true;
    }
}

/**
 * Binds a source with a file.
 *
 * Opcode 10 (0x0a)
 */
class COpcode_FileAddSource extends CMessage
{
    var $file_id = 0;
    var $source_id = 0;

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->file_id = $this->raw_msg->read_raw_int32();
        $this->source_id = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Binds a user with a server.
 *
 * Opcode 12 (0x0c)
 */
class COpcode_ServerUser extends CMessage
{
    var $server_id = 0;
    var $user_id = 0;

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->server_id = $this->raw_msg->read_raw_int32();
        $this->user_id = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Server status.
 *
 * Opcode 13 (0x0d)
 */
class COpcode_ServerState extends CMessage
{
    var $server_id = 0;
    var $status = 0;
    var $rank = 0;

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->server_id = $this->raw_msg->read_raw_int32();
        $this->status = $this->raw_msg->read_raw_int8();

        if( $this->status == 3 || $this->status == 5 || $this->status == 9 )
            $this->rank = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Client info.
 *
 * Opcode 15 (0x0f)
 */
class COpcode_ClientInfo extends CMessage
{
    var $client_id = 0;             ///< Client identifier
    var $network_id = 0;            ///< Client Network Identifier
    var $client_kind = array();     ///< Client Kind (direct or firewalled)
    var $state = array( 0, 0 );     ///< Client Connection State
    var $client_type = 0;           ///< Client Type (0 = Source, 1 = Friend, 2 = Contact)
    var $client_metadata = array(); ///< Client Metadata
    var $client_name = '';          ///< Client Name
    var $client_rating = 0;         ///< Client Rating (not used)
    var $client_software = '';      ///< Client Software
    var $downloaded = 0;            ///< Downloaded
    var $uploaded = 0;              ///< Uploaded
    var $upload_filename = '';      ///< Upload File Name
    var $connect_time = 0;          ///< Connect Time (Date Format?)
    var $emule_mod = '';            ///< Emule Mod
    var $client_release = '';       ///< Client Release (Version)
    var $sui_verified = false;      ///< Sui Verified

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->client_id = $this->raw_msg->read_raw_int32();
        $this->network_id = $this->raw_msg->read_raw_int32();
        $this->client_kind = $this->_read_client_kind();
        $this->state = $this->_read_client_state();
        $this->client_type = $this->raw_msg->read_raw_int8();
        $this->client_metadata = array();

        $num_metadata = $this->raw_msg->read_raw_int16();

        for( $i = 0; $i < $num_metadata; $i++ )
            $this->client_metadata[] = $this->raw_msg->read_raw_tag();

        $this->client_name = $this->raw_msg->read_raw_string();
        $this->client_rating = $this->raw_msg->read_raw_int32();
        $this->client_software = $this->raw_msg->read_raw_string();
        $this->downloaded = $this->raw_msg->read_raw_int64();
        $this->uploaded = $this->raw_msg->read_raw_int64();
        $this->upload_filename = $this->raw_msg->read_raw_string();
        $this->connect_time = $this->raw_msg->read_raw_int32();
        $this->emule_mod = $this->raw_msg->read_raw_string();
        $this->client_release = $this->raw_msg->read_raw_string();
        $this->sui_verified = $this->raw_msg->read_raw_int8() ? true : false;

        return true;
    }

    private function _read_client_kind()
    {
        $client_type = $this->raw_msg->read_raw_int8();

        if( $client_type == 1 )
        {
            $client_name = $this->raw_msg->read_raw_string();
            $client_hash = $this->raw_msg->read_raw_hash();
            $ip_address = $this->raw_msg->read_raw_int32();
            $geoip = $this->raw_msg->read_raw_int8();
            $port = $this->raw_msg->read_raw_int16();

            return array(
                'client_type' => 1,
                'client_name' => $client_name,
                'client_hash' => $client_hash,
                'ip_address' => $ip_address,
                'geoip' => $geoip,
                'port' => $port
            );
        }

        if( $client_type == 0 )
        {
            $ip_address = $this->raw_msg->read_raw_int32();
            $geoip = $this->raw_msg->read_raw_int8();
            $port = $this->raw_msg->read_raw_int16();

            return array(
                'client_type' => 0,
                'ip_address' => $ip_address,
                'geoip' => $geoip,
                'port' => $port
            );

        }

        return array( 'client_type' => $client_type );
    }

    private function _read_client_state()
    {
        static $conn_status_messages = array(
            0 => 'Not connected',
            1 => 'Connecting',
            2 => 'Connected Initiating',
            3 => 'Connected Downloading',
            4 => 'Connected',
            5 => 'Connected and Queued',
            6 => 'New Host',
            7 => 'Removed Host (this host should be removed)',
            8 => 'Black Listed',
            9 => 'Not Connected and was Queued',
            10 => 'Connected and ?'
        );

        $state = $this->raw_msg->read_raw_int8();

        if( in_array( $state, array(3, 5, 9) ) )
        {
            $rank = $this->raw_msg->read_raw_int32();

            return array(
                'state' => $state,
                'state_desc' => $conn_status_messages[$state],
                'rank' => $rank
            );
        }

        return array(
            'state' => $state,
            'state_desc' => $conn_status_messages[$state]
        );
    }
}

/**
 * Client state (only the state of the client has changed)
 *
 * Opcode 16 (0x10)
 */
class COpcode_ClientState extends CMessage
{
    var $client_id = 0;
    var $host_state = array();

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->client_id = $this->raw_msg->read_raw_int32();
        $this->host_state = $this->_read_host_state();

        return true;
    }

    private function _read_host_state()
    {
        static $conn_status_messages = array(
            0 => 'Not connected',
            1 => 'Connecting',
            2 => 'Connected Initiating',
            3 => 'Connected Downloading',
            4 => 'Connected',
            5 => 'Connected and Queued',
            6 => 'New Host',
            7 => 'Removed Host (this host should be removed)',
            8 => 'Black Listed',
            9 => 'Not Connected and was Queued',
            10 => 'Connected and ?'
        );

        $state = $this->raw_msg->read_raw_int8();

        if( in_array( $state, array(3, 5, 9) ) )
        {
            $rank = $this->raw_msg->read_raw_int32();

            return array(
            'state' => $state,
            'state_desc' => $conn_status_messages[$state],
            'rank' => $rank
            );
        }

        return array(
            'state' => $state,
            'state_desc' => $conn_status_messages[$state]
        );
    }
}

/**
 * Console message.
 *
 * Opcode 19 (0x13)
 */
class COpcode_ConsoleMessage extends CMessage
{
    var $message = ''; ///< Console message.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->message = $this->raw_msg->read_raw_string();

        return true;
    }
}

/**
 * Network information.
 *
 * Opcode 20 (0x14)
 */
class COpcode_NetworkInfo extends CMessage
{
    var $network_id = 0;    ///< Network Identifier.
    var $network_name = ''; ///< Network name.
    var $enabled = false;   ///< Whether this network is enabled or not.
    var $cfg_file = '';     ///< Config file path for this network.
    var $uploaded = 0;      ///< Number of bytes uploaded on network.
    var $downloaded = 0;    ///< Number of bytes downloaded on network.
    var $num_servers = 0;   ///< Number of connected servers (only protocol 18 ?).

    /**
     * Network flags (protocol >17).
     *
     * - \c 'NetworkHasServers'     => has well known servers.
     * - \c 'NetworkHasRooms'       => has rooms to chat with other users.
     * - \c 'NetworkHasMultinet'    => files can be downloaded from several networks
     * - \c 'VirtualNetwork'        => not a real network
     * - \c 'NetworkHasSearch'      => searches can be issued
     * - \c 'NetworkHasChat'        => chat between two users
     * - \c 'NetworkHasSupernodes'  => peers can become servers
     * - \c 'NetworkHasUpload'      => upload is implemented
     */
    var $flags = array(
        'NetworkHasServers'     => false,
        'NetworkHasRooms'       => false,
        'NetworkHasMultinet'    => false,
        'VirtualNetwork'        => false,
        'NetworkHasSearch'      => false,
        'NetworkHasChat'        => false,
        'NetworkHasSupernodes'  => false,
        'NetworkHasUpload'      => false
    );

    function expand()
    {
        static $flag_keys = array(
            0 => 'NetworkHasServers',
            1 => 'NetworkHasRooms',
            2 => 'NetworkHasMultinet',
            3 => 'VirtualNetwork',
            4 => 'NetworkHasSearch',
            5 => 'NetworkHasChat',
            6 => 'NetworkHasSupernodes',
            7 => 'NetworkHasUpload'
        );

        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->network_id = $this->raw_msg->read_raw_int32();
        $this->network_name = $this->raw_msg->read_raw_string();
        $this->enabled = $this->raw_msg->read_raw_int8() ? true : false;
        $this->cfg_file = $this->raw_msg->read_raw_string();
        $this->uploaded = $this->raw_msg->read_raw_int64();
        $this->downloaded = $this->raw_msg->read_raw_int64();

        // Only protocol 18? Check if there is more data to parse, just in case
        if( $this->raw_msg->pointer < strlen($this->raw_msg->data) )
        {
            $this->num_servers = $this->raw_msg->read_raw_int32();

            for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            {
                $flag = $this->raw_msg->read_raw_int16();

                if( $flag < 8 )
                    $this->flags[ $flag_keys[$flag] ] = true;

                else
                    $this->flags[ $flag ] = true;
            }
        }

        return true;
    }
}

/**
 * User information.
 *
 * Opcode 21 (0x15)
 */
class COpcode_UserInfo extends CMessage
{
    var $user_id = 0;       ///< User identifier.
    var $md4 = '';          ///< User MD4 (16-char string).
    var $user_name = '';    ///< User name.
    var $address = null;    ///< User address.
    var $port = 0;          ///< User port.
    var $tags = array();    ///< User tags.
    var $server_id = 0;     ///< Server identifier.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->user_id = $this->raw_msg->read_raw_int32();

        $this->md4 = $this->raw_msg->read_raw_hash();
        $this->user_name = $this->raw_msg->read_raw_string();
        $this->address = $this->raw_msg->read_raw_address();
        $this->port = $this->raw_msg->read_raw_int16();

        $this->tags = array();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
        {
            $tag = $this->raw_msg->read_raw_tag();
            $this->tags = array_merge( $this->tags, $tag );
        }

        $this->server_id = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Chat room information.
 *
 * Opcode 22 (0x16) for protocol version <= 3
 * Opcode 31 (0x1f) for protocol version > 3
 */
class COpcode_RoomInfo extends CMessage
{
    var $room_id = 0;       ///< Chat room identifier.
    var $network_id = 0;    ///< Network identifier.
    var $name = '';         ///< Room name.
    var $status = 0;        ///< Room status (0 = opened, 1 = closed, 2 = paused).

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->room_id = $this->raw_msg->read_raw_int32();
        $this->network_id = $this->raw_msg->read_raw_int32();
        $this->name = $this->raw_msg->read_raw_string();
        $this->status = $this->raw_msg->read_raw_int8();

        return true;
    }
}

/**
 * Chat room Message.
 *
 * Opcode 23 (0x17)
 */
class COpcode_RoomMessage extends CMessage
{
    var $room_id = 0;   ///< Chat room identifier.
    var $type = 0;      ///< Message type (0 = server, 1 = public, 2 = private)
    var $source = 0;    ///< Who sent the message.
    var $message = '';  ///< The actual message.

    function expand()
    {
        $this->room_id = $this->raw_msg->read_raw_int32();
        $this->type = $this->raw_msg->read_raw_int8();

        if( $this->type == 1 || $this->type == 2 )
            $this->source = $this->raw_msg->read_raw_int32();

        $this->message = $this->raw_msg->read_raw_string();

        return true;
    }
}

/**
 * Add a user to a room.
 *
 * Opcode 24 (0x18)
 */
class COpcode_RoomAddUser extends CMessage
{
    var $room_id = 0;   ///< Chat room identifier.
    var $user_id = 0;   ///< User identifier.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->room_id = $this->raw_msg->read_raw_int32();
        $this->user_id = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Server information.
 *
 * Opcode 26 (0x1a)
 */
class COpcode_ServerInfo extends CMessage
{
    var $server_id = 0;         ///< Server ID.
    var $network_id = 0;        ///< Network ID.
    var $address = array();     ///< Array containing address information.
    var $port = 0;              ///< Server port.
    var $score = 0;             ///< Score.
    var $metadata = array();    ///< Metadata (array of name=>value).
    var $number_users = 0;      ///< Number of users connected.
    var $number_files = 0;      ///< Number of files indexed.
    var $conn_status = array(); ///< Connection status info (array: status, status_desc, rank).
    var $name = '';             ///< Name sent by the server.
    var $description = '';      ///< Description sent by the server.
    var $preferred = false;     ///< Is a preferred server?
    var $server_version = '';   ///< Version of the software in the server.
    var $max_users = 0;         ///< Max users that can connect to the server.
    var $lowid_users = 0;       ///< Number of low-ID users connected.
    var $soft_limit = 0;        ///< Soft limit
    var $hard_limit = 0;        ///< Hard limit.
    var $ping = 0;              ///< Ping time.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->server_id = $this->raw_msg->read_raw_int32();
        $this->network_id = $this->raw_msg->read_raw_int32();
        $this->address = $this->raw_msg->read_raw_address();
        $this->port = $this->raw_msg->read_raw_int16();
        $this->score = $this->raw_msg->read_raw_int32();

        $metadata_len = $this->raw_msg->read_raw_int16();
        for( $i = 0; $i < $metadata_len; $i++ )
        {
            $tag = $this->raw_msg->read_raw_tag();
            $this->metadata = array_merge( $this->metadata, $tag );
        }

        $this->number_users = $this->raw_msg->read_raw_int64();
        $this->number_files = $this->raw_msg->read_raw_int64();
        $this->conn_status = $this->_read_host_state();
        $this->name = $this->raw_msg->read_raw_string();
        $this->description = $this->raw_msg->read_raw_string();
        $this->preferred = $this->raw_msg->read_raw_int8() ? true : false;
        $this->server_version = $this->raw_msg->read_raw_string();
        $this->max_users = $this->raw_msg->read_raw_int64();
        $this->lowid_users = $this->raw_msg->read_raw_int64();
        $this->soft_limit = $this->raw_msg->read_raw_int64();
        $this->hard_limit = $this->raw_msg->read_raw_int64();
        $this->ping = $this->raw_msg->read_raw_int32();

        return true;
    }

    private function _read_host_state()
    {
        static $conn_status_messages = array(
            0 => 'Not connected',
            1 => 'Connecting',
            2 => 'Connected Initiating',
            3 => 'Connected Downloading',
            4 => 'Connected',
            5 => 'Connected and Queued',
            6 => 'New Host',
            7 => 'Removed Host (this host should be removed)',
            8 => 'Black Listed',
            9 => 'Not Connected and was Queued',
            10 => 'Connected and ?'
        );

        $conn_status = $this->raw_msg->read_raw_int8();

        if( 0 <= $conn_status && $conn_status <= 10 )
            $conn_status_msg = $conn_status_messages[$conn_status];

        else
            $conn_status_msg = 'Unknown';

        if( $conn_status == 3 || $conn_status == 5 || $conn_status == 9 )
        {
            $conn_rank = $this->raw_msg->read_raw_int32();

            return array(
                'status' => $conn_status,
                'status_desc' => $conn_status_msg,
                'rank' => $conn_rank
            );
        }

        return array(
            'status' => $conn_status,
            'status_desc' => $conn_status_msg
        );
    }
}

/**
 * Message from client.
 *
 * Opcode 27 (0x1b)
 */
class COpcode_MessageFromClient extends CMessage
{
    var $client_id = 0;
    var $message = '';

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->client_id = $this->raw_msg->read_raw_int32();
        $this->message = $this->raw_msg->read_raw_string();

        return true;
    }
}

/**
 * Connected servers.
 *
 * Should be an array of COpcode_ServerInfo. Too much redundant code here.
 *
 * Opcode 28 (0x1c)
 */
class COpcode_ConnectedServers extends CMessage
{
    var $servers = array(); ///< Array of ServerInfo

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $serverinfo_len = $this->raw_msg->read_raw_int16();

        for( $i = 0; $i < $serverinfo_len; $i++ )
        {
            $server_id = $this->raw_msg->read_raw_int32();
            $network_id = $this->raw_msg->read_raw_int32();
            $address = $this->raw_msg->read_raw_address();
            $port = $this->raw_msg->read_raw_int16();
            $score = $this->raw_msg->read_raw_int32();

            $metadata = array();
            for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            {
                $tag = $this->raw_msg->read_raw_tag();
                $metadata = array_merge( $metadata, $tag );
            }

            $number_users = $this->raw_msg->read_raw_int64();
            $number_files = $this->raw_msg->read_raw_int64();
            $conn_status = $this->_read_host_state();
            $name = $this->raw_msg->read_raw_string();
            $description = $this->raw_msg->read_raw_string();
            $preferred = $this->raw_msg->read_raw_int8() ? true : false;
            $server_version = $this->raw_msg->read_raw_string();
            $max_users = $this->raw_msg->read_raw_int64();
            $lowid_users = $this->raw_msg->read_raw_int64();
            $soft_limit = $this->raw_msg->read_raw_int64();
            $hard_limit = $this->raw_msg->read_raw_int64();
            $ping = $this->raw_msg->read_raw_int32();

            $this->servers[] = array(
                'server_id' => $server_id,
                'network_id' => $network_id,
                'address' => $address,
                'port' => $port,
                'score' => $score,
                'metadata' => $metadata,
                'number_users' => $number_users,
                'number_files' => $number_files,
                'conn_status' => $conn_status,
                'name' => $name,
                'description' => $description,
                'preferred' => $preferred,
                'server_version' => $server_version,
                'max_users' => $max_users,
                'lowid_users' => $lowid_users,
                'soft_limit' => $soft_limit,
                'hard_limit' => $hard_limit,
                'ping' => $ping
            );
        }
    }

    private function _read_host_state()
    {
        static $conn_status_messages = array(
            0 => 'Not connected',
            1 => 'Connecting',
            2 => 'Connected Initiating',
            3 => 'Connected Downloading',
            4 => 'Connected',
            5 => 'Connected and Queued',
            6 => 'New Host',
            7 => 'Removed Host (this host should be removed)',
            8 => 'Black Listed',
            9 => 'Not Connected and was Queued',
            10 => 'Connected and ?'
        );

        $conn_status = $this->raw_msg->read_raw_int8();

        if( 0 <= $conn_status && $conn_status <= 10 )
            $conn_status_msg = $conn_status_messages[$conn_status];

        else
            $conn_status_msg = 'Unknown';

        if( $conn_status == 3 || $conn_status == 5 || $conn_status == 9 )
        {
            $conn_rank = $this->raw_msg->read_raw_int32();

            return array(
                'status' => $conn_status,
                'status_desc' => $conn_status_msg,
                'rank' => $conn_rank
            );
        }

        return array(
            'status' => $conn_status,
            'status_desc' => $conn_status_msg
        );
    }
}

/**
 * Shared file upload.
 *
 * Opcode 34 (0x22)
 */
class COpcode_SharedFileUpload extends CMessage
{
    var $file_id = 0;   ///< File identifier.
    var $upload = 0;    ///< Upload.
    var $requests = 0;  ///< Requests.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->file_id = $this->raw_msg->read_raw_int32();
        $this->upload = $this->raw_msg->read_raw_int64();
        $this->requests = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Shared file unshared.
 *
 * Opcode 35 (0x23)
 */
class COpcode_SharedFileUnshared extends CMessage
{
    var $file_id = 0;

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->file_id = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Define a new option to appear in the Settings panel.
 *
 * Opcode 36 (0x24)
 */
class COpcode_AddSectonOption extends CMessage
{
    var $section = '';          ///< Section where the option should appear.
    var $description = '';      ///< Description of the option.
    var $name = '';             ///< Name of the option.
    var $type = '';             ///< Type of the value ("Bool", "Filename", ...).
    var $help = '';             ///< Help for the user.
    var $current_value = '';    ///< Current option value.
    var $default_value = '';    ///< Default option value.
    var $advanced = false;      ///< Advanced option.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->section = $this->raw_msg->read_raw_string();
        $this->description = $this->raw_msg->read_raw_string();
        $this->name = $this->raw_msg->read_raw_string();
        $this->type = $this->raw_msg->read_raw_string();
        $this->help = $this->raw_msg->read_raw_string();
        $this->current_value = $this->raw_msg->read_raw_string();
        $this->default_value = $this->raw_msg->read_raw_string();
        $this->advanced = $this->raw_msg->read_raw_int8() ? true : false;

        return false;
    }
}

/**
 * Define a new plugin option to appear in the Settings panel.
 *
 * Same as COpcode_AddSectonOption?
 *
 * Opcode 38 (0x26)
 */
class COpcode_AddPluginOption extends COpcode_AddSectonOption
{
    // Same implementation as COpcode_AddSectonOption
}

/**
 * File download update.
 *
 * Opcode 46 (0x2e)
 */
class COpcode_FileDownloadUpdate extends CMessage
{
    var $file_id = 0;           ///< File identifier.
    var $download_size = 0;     ///< Downloaded bytes.
    var $download_rate = 0.0;   ///< Download rate.
    var $last_seen = 0;         ///< Seconds since last seen.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->file_id = $this->raw_msg->read_raw_int32();
        $this->download_size = $this->raw_msg->read_raw_int64();
        $this->download_rate = $this->raw_msg->read_raw_float();
        $this->last_seen = $this->raw_msg->read_raw_int32();

        return false;
    }
}

/**
 * User sent a wrong password.
 *
 * Opcode 47 (0x2f)
 */
class COpcode_BadPassword extends CMessage
{
    // No payload.

    function expand()
    {
        return true;
    }
}

/**
 * Shared file info (a file appears here when the first chunk is finished).
 *
 * Opcode 33 (0x21) for old protocol versions.
 * Opcode 48 (0x30)
 */
class COpcode_SharedFileInfo extends CMessage
{
    var $file_id = 0;       ///< File identifier (not compatible with id from COpcode_FileInfo).
    var $network_id = 0;    ///< Network identifier.
    var $file_name = '';    ///< Shared file name.
    var $file_size = 0;     ///< File size.
    var $uploaded = 0;      ///< Number of bytes uploaded.
    var $requests = 0;      ///< Number of requests for this file.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->file_id = $this->raw_msg->read_raw_int32();
        $this->network_id = $this->raw_msg->read_raw_int32();
        $this->file_name = $this->raw_msg->read_raw_string();
        $this->file_size = $this->raw_msg->read_raw_int64();
        $this->uploaded = $this->raw_msg->read_raw_int64();
        $this->requests = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Client stats.
 *
 * Opcode 25 (0x19) for old protocol version.
 * Opcode 37 (0x25) for old protocol version.
 * Opcode 39 (0x27) for old protocol version.
 * Opcode 49 (0x31)
 */
class COpcode_ClientStats extends CMessage
{
    var $total_uploaded = 0;            ///< Total uploaded bytes.
    var $total_downloaded = 0;          ///< Total downloaded bytes.
    var $total_shared = 0;              ///< Total bytes shared.
    var $shared_files = 0;              ///< Number of shared files.
    var $tcp_up_rate = 0;               ///< TCP upload rate.
    var $tcp_down_rate = 0;             ///< TCP download rate.
    var $udp_up_rate = 0;               ///< UDP upload rate.
    var $udp_down_rate = 0;             ///< UDP download rate.
    var $num_current_downloads = 0;     ///< Number of current downloads.
    var $num_downloads_finished = 0;    ///< Number of downloads finished.
    var $connected_servers = array();   ///< Network identifiers and the corresponding number of servers connected to.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->total_uploaded = $this->raw_msg->read_raw_int64();
        $this->total_downloaded = $this->raw_msg->read_raw_int64();
        $this->total_shared = $this->raw_msg->read_raw_int64();
        $this->shared_files = $this->raw_msg->read_raw_int32();
        $this->tcp_up_rate = $this->raw_msg->read_raw_int32();
        $this->tcp_down_rate = $this->raw_msg->read_raw_int32();
        $this->udp_up_rate = $this->raw_msg->read_raw_int32();
        $this->udp_down_rate = $this->raw_msg->read_raw_int32();
        $this->num_current_downloads = $this->raw_msg->read_raw_int32();
        $this->num_downloads_finished = $this->raw_msg->read_raw_int32();

        $num_networks = $this->raw_msg->read_raw_int16();

        for( $i = 0; $i < $num_networks; $i++ )
        {
            $network_id = $this->raw_msg->read_raw_int32();
            $servers = $this->raw_msg->read_raw_int32();

            $this->connected_servers[$network_id] = $servers;
        }

        return true;
    }
}

/**
 * Remove File Source.
 *
 * Opcode 50 (0x32)
 */
class COpcode_FileRemoveSource extends CMessage
{
    var $file_id = 0;   ///< File identifier.
    var $client_id = 0; ///< Client identifier.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->file_id = $this->raw_msg->read_raw_int32();
        $this->client_id = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Clean tables.
 *
 * Opcode 51 (0x33)
 */
class COpcode_CleanTables extends CMessage
{
    var $useful_clients = array();  ///< Client identifiers that are still useful.
    var $useful_servers = array();  ///< Server identifiers that are still useful.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->useful_clients = $this->useful_servers = array();

        $num_clients = $this->raw_msg->read_raw_int16();

        for( $i = 0; $i < $num_clients; $i++ )
            $this->useful_clients[] = $this->raw_msg->read_raw_int32();

        $num_servers = $this->raw_msg->read_raw_int16();

        for( $i = 0; $i < $num_servers; $i++ )
            $this->useful_servers[] = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * File information of download list (for one file).
 *
 * Opcode 7 (0x07) for old protocol versions.\n
 * Opcode 40 (0x28) for old protocol versions.\n
 * Opcode 43 (0x2b) for old protocol versions.\n
 * Opcode 52 (0x34)
 */
class COpcode_FileInfo extends CMessage
{
    var $file_id = 0;               ///< File identifier.
    var $network_id = 0;            ///< Network identifier.
    var $filenames = array();       ///< Possible file names.
    var $md4 = '';                  ///< File MD4 hash.
    var $file_size = 0;             ///< Size in bytes.
    var $downloaded = 0;            ///< Downloaded bytes.
    var $sources = 0;               ///< Number of sources.
    var $clients = 0;               ///< Number of clients.
    var $status = array();          ///< The status of the download.
    var $chunks = '';               ///< One char by chunk: 0 = missing, 1 = partial, 2 = complete, 3 = verified.
    var $availability = array();    ///< Availability of chunks by network.
    var $download_rate = 0.0;       ///< Current download speed.
    var $chunk_ages = array();      ///< Chunk ages in seconds.
    var $file_age = 0;              ///< Seconds since download started.
    var $file_format = array();     ///< File format.
    var $name = '';                 ///< Preferred file name.
    var $last_seen_complete = 0;    ///< Last seen complete (seconds).
    var $priority = 0;              ///< File priority (e.g. very low: -20, normal: 0, high: 10)
    var $comment = '';              ///< File comment
    var $links = array();           ///< Network specific links to the file.
    var $subfiles = array();        ///< List of name, size and format of the subfiles.
    var $file_format_im = '';       ///< File format given by ImageMagic library.
    var $comments = array();        ///< List of IP, GeoIP, name, rating and comment.
    var $user = '';                 ///< Owner user of the file.
    var $group = '';                ///< Owner group of the file.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->file_id = $this->raw_msg->read_raw_int32();
        $this->network_id = $this->raw_msg->read_raw_int32();

        $this->filenames = array();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            $this->filenames[] = $this->raw_msg->read_raw_string();

        $this->md4 = $this->raw_msg->read_raw_hash();
        $this->file_size = $this->raw_msg->read_raw_int64();
        $this->downloaded = $this->raw_msg->read_raw_int64();
        $this->sources = $this->raw_msg->read_raw_int32();
        $this->clients = $this->raw_msg->read_raw_int32();

        $file_status = $this->raw_msg->read_raw_int8();

        if( $file_status == 6 )
            $reason = $this->raw_msg->read_raw_string();

        else
            $reason = '';

        $this->status = array(
            'file_status' => $file_status,
            'abort_reason' => $reason
        );

        $this->chunks = $this->raw_msg->read_raw_string();
        $this->availability = array();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
        {
            $network_num = $this->raw_msg->read_raw_int32();
            $availability = $this->raw_msg->read_raw_string();
            $availability = array_map( ord, str_split( $availability ) );
            $this->availability[$network_num] = $availability;
        }

        $this->download_rate = $this->raw_msg->read_raw_float();
        $this->chunk_ages = array();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            $this->chunk_ages[] = $this->raw_msg->read_raw_int32();

        $this->file_age = $this->raw_msg->read_raw_int32();
        $this->file_format = $this->_read_file_format();
        $this->name = $this->raw_msg->read_raw_string();
        $this->last_seen_complete = $this->raw_msg->read_raw_int32();
        $this->priority = $this->raw_msg->read_raw_int32();
        $this->comment = $this->raw_msg->read_raw_string();

        $this->links = array();
        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            $this->links[] = $this->raw_msg->read_raw_string();

        $this->subfiles = array();
        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
        {
            $name = $this->raw_msg->read_raw_string();
            $size = $this->raw_msg->read_raw_int64();
            $format = $this->raw_msg->read_raw_string();

            $this->subfiles[] = array(
                'name' => $name,
                'size' => $size,
                'format' => $format
            );
        }

        // Following values are part of protocol 40 an later
        $this->file_format_im = $this->raw_msg->read_raw_string();

        $this->comments = array();
        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
        {
            $ip = $this->raw_msg->read_raw_int32();
            $geoip = $this->raw_msg->read_raw_int8();
            $name = $this->raw_msg->read_raw_string();
            $rating = $this->raw_msg->read_raw_int8();
            $comment = $this->raw_msg->read_raw_string();

            $this->comments[] = array(
                'ip' => long2ip($ip),
                'geoip' => $geoip,
                'name' => $name,
                'rating' => $rating,
                'comment' => $comment
            );
        }

        $this->user = $this->raw_msg->read_raw_string();
        $this->group = $this->raw_msg->read_raw_string();

        return true;
    }

    private function _read_file_format()
    {
        $file_format = $this->raw_msg->read_raw_int8();

        switch( $file_format )
        {
            case 1:
                $extension = $this->raw_msg->read_raw_string();
                $kind = $this->raw_msg->read_raw_string();

                return array(
                    'format' => $file_format,
                    'extension' => $extension,
                    'kind' => $kind
                );
                break;

            case 2:
                $video_codec = $this->raw_msg->read_raw_string();
                $video_width = $this->raw_msg->read_raw_int32();
                $video_height = $this->raw_msg->read_raw_int32();
                $video_fps = $this->raw_msg->read_raw_int32();
                $video_rate = $this->raw_msg->read_raw_int32();

                return array(
                    'format' => $file_format,
                    'video_codec' => $video_codec,
                    'video_width' => $video_width,
                    'video_height' => $video_height,
                    'video_fps' => $video_fps,
                    'video_rate' => $video_rate
                );
                break;

            case 3:
                $title = $this->raw_msg->read_raw_string();
                $artist = $this->raw_msg->read_raw_string();
                $album = $this->raw_msg->read_raw_string();
                $year = $this->raw_msg->read_raw_string();
                $comment = $this->raw_msg->read_raw_string();
                $tracknum = $this->raw_msg->read_raw_int32();
                $genre = $this->raw_msg->read_raw_int32();

                return array(
                    'format' => $file_format,
                    'title' => $title,
                    'artist' => $artist,
                    'album' => $album,
                    'year' => intval( $year ),
                    'comment' => $comment,
                    'tracknum' => $tracknum,
                    'genre' => $genre
                );
                break;

            case 4:
                $streams = array();

                for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
                    $streams += $this->_read_ogg_stream_tags();

                return $streams;
                break;
        }
    }

    private function _read_ogg_stream_tags()
    {
        static $tag_types = array(
            0 => 'codec',
            1 => 'bits_per_sample',
            2 => 'duration',
            5 => 'audio_channels',
            6 => 'audio_sample_rate',
            7 => 'audio_block_align',
            8 => 'audio_average_bytes_per_second',
            9 => 'vorbis_version',
            10 => 'vorbis_sample_rate',
            11 => 'bitrate',
            12 => 'vorbis_block_size_0',
            13 => 'vorbis_block_size_1',
            14 => 'video_width',
            15 => 'video_height',
            16 => 'video_sample_rate',
            17 => 'video_aspect_ratio',
            18 => 'theora_cs',
            19 => 'theora_quality',
            20 => 'theora_average_bytes_per_second'
        );

        static $stream_types = array(
            0 => 'video',
            1 => 'audio',
            2 => 'text',
            3 => 'index',
            4 => 'vorbis',
            5 => 'theora'
        );

        $stream_num = $this->raw_msg->read_raw_int32();
        $stream_type = $this->raw_msg->read_raw_int8();
        $stream_tags = array();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
        {
            $type = $this->raw_msg->read_raw_int8();

            switch( $type )
            {
                case 0; // Codec
                    $tag = $this->raw_msg->read_raw_string();

                    $key = $tag_types[ $type ];
                    $stream_tags[$key] = $tag;
                    break;

                case 1:  // Bits per sample
                case 2:  // Duration
                case 5:  // Audio channels
                case 7:  // Audio block align
                case 12: // Vorbis block size 0
                case 13: // Vorbis block size 1
                case 19: // Theora quality
                case 20: // Theora average bytes per second
                    $tag = $this->raw_msg->read_raw_int32();

                    $key = $tag_types[ $type ];
                    $stream_tags[$key] = $tag;
                    break;

                case 6:  // Audio sample rate
                case 8:  // Audio average bytes per second
                case 9:  // Vorbis version
                case 10: // Vorbis sample rate
                case 14: // Video width
                case 15: // Video height
                case 16: // Video sample rate
                case 17: // Video aspect ratio
                    $tag = $this->raw_msg->read_raw_float();

                    $key = $tag_types[ $type ];
                    $stream_tags[$key] = $tag;
                    break;

                case 18: // Theora CS
                    $cs = $this->raw_msg->read_raw_int8();

                    if( $cs == 0 )
                        $tag = 'Undefined';

                    elseif( $cs == 1 )
                        $tag = '470M';

                    elseif( $cs == 2 )
                        $tag = '470BG';

                    else
                        $tag = sprintf( 'Unknown (%u)', $cs );

                    $key = $tag_types[ $type ];
                    $stream_tags[$key] = $tag;
                    break;

                case 11:
                    $tag = array();
                    for( $j = $this->raw_msg->read_raw_int16(); $j > 0; $j-- )
                    {
                        $br_type = $this->raw_msg->read_raw_int8();
                        $bitrate = $this->raw_msg->read_raw_float();

                        if( $br_type == 0 )
                            $tag['max_bitrate'] = $bitrate;

                        elseif( $br_type == 1 )
                            $tag['nom_bitrate'] = $bitrate;

                        elseif( $br_type == 2 )
                            $tag['min_bitrate'] = $bitrate;
                    }

                    $key = $tag_types[ $type ];
                    $stream_tags[$key] = $tag;
                    break;
            }
        }

        return array(
            $stream_num => array(
                'type' => $stream_types[ $stream_type ],
                'tags' => $stream_tags
            )
        );
    }
}

/**
 * All the files being downloading.
 *
 * Opcode 29 (0x1d) for old protocol verions.\n
 * Opcode 41 (0x29) for old protocol verions.\n
 * Opcode 44 (0x2c) for old protocol verions.\n
 * Opcode 53 (0x35)
 */
class COpcode_DownloadingFiles extends COpcode_FileInfo
{
    var $downloads = array();

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $num_downloads = $this->raw_msg->read_raw_int16();
        for( $i = 0; $i < $num_downloads; $i++ )
        {
            $this->tmp = array();
            parent::expand();

            $this->downloads[$this->file_id] = array(
                'file_id'               => $this->file_id,
                'network_id'            => $this->network_id,
                'filenames'             => $this->filenames,
                'md4'                   => $this->md4,
                'file_size'             => $this->file_size,
                'downloaded'            => $this->downloaded,
                'sources'               => $this->sources,
                'clients'               => $this->clients,
                'status'                => $this->status,
                'chunks'                => $this->chunks,
                'availability'          => $this->availability,
                'downlaod_rate'         => $this->downlaod_rate,
                'chunk_ages'            => $this->chunk_ages,
                'file_age'              => $this->file_age,
                'file_format'           => $this->file_format,
                'name'                  => $this->name,
                'last_seen_complete'    => $this->last_seen_complete,
                'priority'              => $this->priority,
                'comment'               => $this->comment,
                'links'                 => $this->links,
                'subfiles'              => $this->subfiles,
                'file_format_im'        => $this->file_format_im,
                'comments'              => $this->comments,
                'user'                  => $this->user,
                'group'                 => $this->group
            );
        }

        unset(
            $this->file_id,
            $this->network_id,
            $this->filenames,
            $this->md4,
            $this->file_size,
            $this->downloaded,
            $this->sources,
            $this->clients,
            $this->status,
            $this->chunks,
            $this->availability,
            $this->downlaod_rate,
            $this->chunk_ages,
            $this->file_age,
            $this->file_format,
            $this->name,
            $this->last_seen_complete,
            $this->priority,
            $this->comment,
            $this->links,
            $this->subfiles,
            $this->file_format_im,
            $this->comments,
            $this->user,
            $this->group
        );

        return true;
    }
}

/**
 * All the files downloaded, waiting for commit.
 *
 * Opcode 30 (0x1e) for old protocol verions.
 * Opcode 42 (0x2a) for old protocol verions.
 * Opcode 45 (0x2d) for old protocol verions.
 * Opcode 54 (0x36)
 *
 * @note Implementation is same as COpcode_DownloadingFiles
 */
class COpcode_DownloadedFiles extends COpcode_DownloadingFiles
{
    // Implementation is same as COpcode_DownloadingFiles
}

/**
 * Uploading files.
 *
 * Opcode 55 (0x37)
 */
class COpcode_Uploaders extends CMessage
{
    var $files = array();   ///< All the files being uploaded.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            $this->files[] = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Pending files.
 *
 * Opcode 56 (0x38)
 */
class COpcode_Pending extends CMessage
{
    var $files = array();   ///< Pending File Identifiers.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->files = array();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
            $this->files[] = $this->raw_msg->read_raw_int32();

        return true;
    }
}

/**
 * Opcode 57 (0x39)
 *
 * TODO: Not implemented (no documentation available).
 */
class COpcode_Search extends CMessage
{
    function expand()
    {
        return false;
    }
}

/**
 * Core version.
 *
 * Opcode 58 (0x3a)
 */
class COpcode_Version extends CMessage
{
    var $version = ''; ///< Core version as a string.

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->version = $this->raw_msg->read_raw_string();

        return true;
    }
}

/**
 * Statistics.
 *
 * Opcode 59 (0x3b)
 *
 * TODO: Implemented, but fields are unknown.
 */
class COpcode_Stats extends CMessage
{
    var $id = 0;            ///< ID?
    var $stats = array();   ///< Stats

    function expand()
    {
        if( $this->raw_msg === null && !$this->read() )
            return false;

        $this->id = $this->raw_msg->read_raw_int32();
        $this->stats = array();

        for( $i = $this->raw_msg->read_raw_int16(); $i > 0; $i-- )
        {
            $str = $this->raw_msg->read_raw_string();
            $int = $this->raw_msg->read_raw_int32();

            $statinfo = array();

            for( $j = $this->raw_msg->read_raw_int16(); $j > 0; $j-- )
                $statinfo[] = $this->_read_statinfo();

            $this->stats[] = array( $str, $int, $statinfo );
        }

        return true;
    }

    private function _read_statinfo()
    {
        $lngstr     = $this->raw_msg->read_raw_string();
        $srtstr     = $this->raw_msg->read_raw_string();
        $last_seen  = $this->raw_msg->read_raw_int32();
        $banned     = $this->raw_msg->read_raw_int32();
        $reqs       = $this->raw_msg->read_raw_int32();
        $downloaded = $this->raw_msg->read_raw_int64();
        $uploaded   = $this->raw_msg->read_raw_int64();

        return array(
            'long string'   => $lngstr,
            'short string'  => $srtstr,
            'last seen'     => $lastseen,
            'banned'        => $banned,
            'requests'      => $reqs,
            'downloaded'    => $downloaded,
            'uploaded'      => $uploaded
        );
    }
}
