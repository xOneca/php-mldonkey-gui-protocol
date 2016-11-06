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
 * @file
 * Messages sent to the server.
 */

/**
 * Protocol version information.
 *
 * Opcode 0 (0x00)
 */
class COpcode_ProtocolVersion extends CMessage
{
    var $opcode = 0;

    var $version = 41;

    function build()
    {
        if( $this->raw_msg === null )
            $this->raw_msg = new CRaw_Data('');

        $this->raw_msg->write_raw_int32( $this->version );

        return true;
    }
}
/**
 * Connect to more servers?
 *
 * Opcode 1 (0x01)
 */
class COpcode_ConnectMore extends CMessage
{
    var $opcode = 1;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Clean old servers.
 *
 * Opcode 2 (0x02)
 */
class COpcode_CleanOldServers extends CMessage
{
    var $opcode = 2;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Kill server
 *
 * Opcode 3 (0x03)
 */
class COpcode_KillServer extends CMessage
{
    var $opcode = 3;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Extended search
 *
 * Opcode 4 (0x04)
 */
class COpcode_ExtendedSearch extends CMessage
{
    var $opcode = 4;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Download file by URL.
 *
 * Opcode 8 (0x08)
 */
class COpcode_DlLink extends CMessage
{
    var $opcode = 8;

    var $url = '';

    function __construct( &$socket, $url )
    {
        parent::__construct( $socket );

        $this->url = $url;
    }

    function build()
    {
        $this->raw_msg->write_raw_string( $this->url );

        return true;
    }
}

/**
 * Remove server
 *
 * Opcode 9 (0x09)
 */
class COpcode_RemoveServer extends CMessage
{
    var $opcode = 9;

    var $server_id = 0;

    function __construct( &$socket, $server_id )
    {
        parent::__construct( $socket );

        $this->server_id = $server_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->server_id );

        return true;
    }
}

/**
 * Save options
 *
 * Opcode 10 (0x0a)
 */
class COpcode_SaveOptions extends CMessage
{
    var $opcode = 10;

    var $options = array();

    function __construct( &$socket, $initial_options )
    {
        parent::__construct( $socket );

        $this->options = $initial_options;
    }

    function build()
    {
        $this->raw_msg->write_raw_int16(count($this->options));

        foreach( $this->options as $option_name => $option_value )
        {
            $this->raw_msg->write_raw_string( $option_name );
            $this->raw_msg->write_raw_string( $option_value );
        }

        return true;
    }
}

/**
 * Remove download.
 *
 * Opcode 11 (0x0b)
 */
class COpcode_RemoveDownload extends CMessage
{
    var $opcode = 11;

    var $file_id = 0;

    function __construct( &$socket, $file_id )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );

        return true;
    }
}

/**
 * Get server users
 *
 * Opcode 12 (0x0c)
 * Opcode 32 (0x20)
 */
class COpcode_GetServerUsers extends CMessage
{
    var $opcode = 12;

    var $server_id = 0;

    function __construct( &$socket, $server_id )
    {
        parent::__construct( $socket );

        $this->server_id = $server_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->server_id );

        return true;
    }
}

/**
 * Save file as... (same as opcode 56?)
 *
 * Opcode 13 (0x0d)
 */
class COpcode_SaveFileAs extends CMessage
{
    var $opcode = 13;

    var $file_id = 0;
    var $new_name = '';

    function __construct( &$socket, $file_id, $new_name )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
        $this->new_name = $new_name;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );
        $this->raw_msg->write_raw_string( $this->new_name );

        return true;
    }
}

/**
 * Add client as friend
 *
 * Opcode 14 (0x0e)
 */
class COpcode_AddClientFriend extends CMessage
{
    var $opcode = 14;

    var $client_id = 0;

    function __construct( &$socket, $client_id )
    {
        parent::__construct( $socket );

        $this->client_id = $client_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->client_id );

        return true;
    }
}

/**
 * Add user as friend
 *
 * Opcode 15 (0x0f)
 */
class COpcode_AddUserFriend extends COpcode_AddClientFriend
{
    var $opcode = 15;

    // Same implementation as COpcode_AddClientFriend
}

/**
 * Remove friend
 *
 * Opcode 16 (0x10)
 */
class COpcode_RemoveFriend extends CMessage
{
    var $opcode = 16;

    var $client_id = 0;

    function __construct( &$socket, $client_id )
    {
        parent::__construct( $socket );

        $this->client_id = $client_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->client_id );

        return true;
    }
}

/**
 * Remove all friends
 *
 * Opcode 17 (0x11)
 */
class COpcode_RemoveAllFriends extends CMessage
{
    var $opcode = 17;

    function __construct( &$socket )
    {
        parent::__construct( $socket );
    }

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Find a friend
 *
 * Opcode 18 (0x12)
 */
class COpcode_FindFriend extends CMessage
{
    var $opcode = 18;

    var $name = '';

    function __construct( &$socket, $name )
    {
        parent::__construct( $socket );

        $this->name = $name;
    }

    function build()
    {
        $this->raw_msg->write_raw_string( $this->name );

        return true;
    }
}

/**
 * View users of a server
 *
 * Opcode 19 (0x13)
 */
class COpcode_ViewUsers extends CMessage
{
    var $opcode = 19;

    var $server_id = 0;

    function __construct( &$socket, $server_id )
    {
        parent::__construct( $socket );

        $this->server_id = $server_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->server_id );

        return true;
    }
}

/**
 * Connect all
 *
 * Opcode 20 (0x14)
 */
class COpcode_ConnectAll extends CMessage
{
    var $opcode = 20;

    var $file_id = 0;

    function __construct( &$socket, $file_id )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );

        return true;
    }
}

/**
 * Connect to a server
 *
 * Opcode 21 (0x15)
 */
class COpcode_ConnectServer extends CMessage
{
    var $opcode = 21;

    var $server_id = 0;

    function __construct( &$socket, $server_id )
    {
        parent::__construct( $socket );

        $this->server_id = $server_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->server_id );

        return true;
    }
}

/**
 * Disconnect from a server
 *
 * Opcode 22 (0x16)
 */
class COpcode_DisconnectServer extends CMessage
{
    var $opcode = 22;

    var $server_id = 0;

    function __construct( &$socket, $server_id )
    {
        parent::__construct( $socket );

        $this->server_id = $server_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->server_id );

        return true;
    }
}

/**
 * Pause/Resume a download
 *
 * Opcode 23 (0x17)
 */
class COpcode_SwitchDownload extends CMessage
{
    var $opcode = 23;

    var $file_id = 0;
    var $paused = false;

    function __construct( &$socket, $file_id, $paused )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
        $this->paused = $paused ? true : false;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );
        $this->raw_msg->write_raw_int8( !$this->paused ); // 0 = pause; 1 = resume

        return true;
    }
}

/**
 * Verify all downloaded chunks
 *
 * Opcode 24 (0x18)
 */
class COpcode_VerifyAllChunks extends CMessage
{
    var $opcode = 24;

    var $file_id = 0;

    function __construct( &$socket, $file_id )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );

        return true;
    }
}
/**
 * Query format
 *
 * Opcode 25 (0x19)
 */
class COpcode_QueryFormat extends CMessage
{
    // Not documented
}

/**
 * Modify MP3 tags
 *
 * Opcode 26 (0x1a)
 */
class COpcode_ModifyMp3Tags extends CMessage
{
    // Not documented
}

// Opcode 27 == Opcode 53 (CloseSearch)

/**
 * Set option
 *
 * Opcode 28 (0x1c)
 */
class COpcode_SetOption extends CMessage
{
    var $opcode = 28;

    var $name = '';  ///< Option name
    var $value = ''; ///< Option value

    function __construct( &$socket, $name, $value )
    {
        parent::__construct( $socket );

        $this->name = $name;
        $this->value = $value;
    }

    function build()
    {
        $this->raw_msg->write_raw_string( $this->name );
        $this->raw_msg->write_raw_string( $this->value );

        return true;
    }
}

/**
 * Execute console command
 *
 * Opcode 29 (0x1d)
 */
class COpcode_ConsoleCommand extends CMessage
{
    var $opcode = 29;

    var $command = '';

    function __construct( &$socket, $command )
    {
        parent::__construct( $socket );

        $this->command = $command;
    }

    function build()
    {
        $this->raw_msg->write_raw_string( $this->command );

        return true;
    }
}

/**
 * Get preview of file
 * MLDonkey Wiki: Does not work, use buildin http server instead
 *
 * Opcode 30 (0x1e)
 */
class COpcode_Preview extends CMessage
{
    var $opcode = 30;

    var $file_id = 0;

    function __construct( &$socket, $file_id )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );

        return true;
    }
}

/**
 * Connect to a friend
 *
 * Opcode 31 (0x1f)
 */
class COpcode_ConnectFriend extends CMessage
{
    var $opcode = 31;

    var $client_id = 0;

    function __construct( &$socket, $client_id )
    {
        parent::__construct( $socket );

        $this->client_id = $client_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->client_id );

        return true;
    }
}

// Opcode 32 == Opcode 12 (GetServerUsers)

/**
 * Get client files
 *
 * Opcode 33 (0x21)
 */
class COpcode_GetClientFiles extends CMessage
{
    var $opcode = 33;

    var $client_id = 0;

    function __construct( &$socket, $client_id )
    {
        parent::__construct( $socket );

        $this->client_id = $client_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->client_id );

        return true;
    }
}

/**
 * Get file locations
 *
 * Opcode 34 (0x22)
 */
class COpcode_GetFileLocations extends CMessage
{
    var $opcode = 34;

    var $file_id = 0;

    function __construct( &$socket, $file_id )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );

        return true;
    }
}

/**
 * Get server information
 *
 * Opcode 35 (0x23)
 */
class COpcode_GetServerInfo extends CMessage
{
    var $opcode = 35;

    var $server_id = 0;

    function __construct( &$socket, $server_id )
    {
        parent::__construct( $socket );

        $this->server_id = $server_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->server_id );

        return true;
    }
}

/**
 * Get client information
 *
 * Opcode 36 (0x24)
 */
class COpcode_GetClientInfo extends CMessage
{
    var $opcode = 36;

    var $client_id = 0;

    function __construct( &$socket, $client_id )
    {
        parent::__construct( $socket );

        $this->client_id = $client_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->client_id );

        return true;
    }
}

/**
 * Get file information
 *
 * Opcode 37 (0x25)
 */
class COpcode_GetFileInfo extends CMessage
{
    var $opcode = 37;

    var $file_id = 0;

    function __construct( &$socket, $file_id )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );

        return true;
    }
}

/**
 * Get user information
 *
 * Opcode 38 (0x26)
 */
class COpcode_GetUserInfo extends CMessage
{
    var $opcode = 38;

    var $user_id = 0;

    function __construct( &$socket, $user_id )
    {
        parent::__construct( $socket );

        $this->user_id = $user_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->user_id );

        return true;
    }
}

/**
 * Enable/Disable network
 *
 * Opcode 40 (0x28)
 */
class COpcode_EnableNetwork extends CMessage
{
    var $opcode = 40;

    var $network_id = 0;
    var $enabled = true; // 0 = disable; 1 = enable

    function __construct( &$socket, $network_id, $enabled )
    {
        parent::__construct( $socket );

        $this->network_id = $network_id;
        $this->enabled = $enabled ? true : false;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->network_id );
        $this->raw_msg->write_raw_int8( $this->enabled );

        return true;
    }
}

/**
 * Browse user (??)
 *
 * Opcode 41 (0x29)
 */
class COpcode_BrowseUser extends CMessage
{
    var $opcode = 41;

    var $user_id = 0;

    function __construct( &$socket, $user_id )
    {
        parent::__construct( $socket );

        $this->user_id = $user_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->user_id );

        return true;
    }
}

/**
 * Search query
 *
 * Opcode 42 (0x2a)
 */
class COpcode_SearchQuery extends CMessage
{
    var $opcode = 42;

    var $search_id = 0;
    var $query = null;
    var $max_results = 0;
    var $search_type = 0;   ///< 0 = local; 1 = remote; 2 = subscription
    var $network = 0;       ///< 0 == All

    function __construct( &$socket, $search_id, $query, $max_results, $search_type, $network )
    {
        parent::__construct( $socket );

        $this->search_id = $search_id;
        $this->query = $query;
        $this->max_results = $max_results;
        $this->search_type = $search_type;
        $this->network = $network;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->search_id );
        $this->_build_query( $this->query );
        $this->raw_msg->write_raw_int32( $this->max_results );
        $this->raw_msg->write_raw_int8( $this->search_type );
        $this->raw_msg->write_raw_int32( $this->network );

        return true;
    }

    private function _build_query( $query )
    {
        $this->raw_msg->write_raw_int8( $query['type'] );

        switch( $query['type'] )
        {
            case 0: // AND
            case 1: // OR
            case 13: // Hidden fields
                $this->raw_msg->write_raw_int16( count($query['queries']) );

                foreach( $query['queries'] as $q )
                    $this->_build_query( $q );

                break;

            case 2: // AND NOT
                $this->_build_query( $query['queries'][0] );
                $this->_build_query( $query['queries'][1] );
                break;

            case 3: // Module (fields to be displayed in a frame)
                $this->raw_msg->write_raw_string( $query['module'] );
                $this->_build_query( $query['query'] );
                break;

            default:
                $this->raw_msg->write_raw_string( $query['comment'] );
                $this->raw_msg->write_raw_string( $query['default'] );
                break;
        }
    }
}

/**
 * Message to client
 *
 * Opcode 43 (0x2b)
 */
class COpcode_MessageToClient extends CMessage
{
    var $opcode = 43;

    var $client_id = 0;
    var $message = '';

    function __construct( &$socket, $client_id, $message )
    {
        parent::__construct( $socket );

        $this->client_id = $client_id;
        $this->message = $message;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->client_id );
        $this->raw_msg->write_raw_string( $this->message );

        return true;
    }
}

/**
 * Get connected servers
 *
 * Opcode 44 (0x2c)
 */
class COpcode_GetConnectedServers extends CMessage
{
    var $opcode = 44;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Get downloading files
 *
 * Opcode 45 (0x2d)
 */
class COpcode_GetDownloadingFiles extends CMessage
{
    var $opcode = 45;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Get downloaded files
 *
 * Opcode 46 (0x2e)
 */
class COpcode_GetDownloadedFiles extends CMessage
{
    var $opcode = 46;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * GUI extensions
 *
 * The Core does not seem to generate a response to this OpCode.
 * This opcode must be sent before password, along with protocol version.
 *
 * Opcode 47 (0x2f)
 */
class COpcode_GuiExtensions extends CMessage
{
    var $opcode = 47;

    var $extensions = array();

    function __construct( &$socket, $extensions = array() )
    {
        parent::__construct( $socket );

        $this->extensions = $extensions;
    }

    function build()
    {
        foreach( $this->extensions as $extension )
        {
            $this->raw_msg->write_raw_int32( $extension[0] );
            $this->raw_msg->write_raw_int8( $extension[1] );
        }

        return true;
    }

    function enable_poll_mode()
    {
        $this->extensions[] = array( 1, 1 );
    }
}

/**
 * Refresh upload stats
 *
 * Opcode 49 (0x31)
 */
class COpcode_RefreshUploadStats extends CMessage
{
    var $opcode = 49;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Start a download
 *
 * Opcode 50 (0x32)
 */
class COpcode_Download extends CMessage
{
    var $opcode = 50;

    var $names = array();   ///< All known names for the file
    var $file_id = 0;       ///< Identifier of the result to download
    var $force = false;     ///< 0 = try; 1 = force download

    function __construct( &$socket, $names, $file_id, $force )
    {
        parent::__construct( $socket );

        $this->names = $names;
        $this->file_id = $file_id;
        $this->force = $force ? true : false;
    }

    function build()
    {
        $this->raw_msg->write_raw_int16( count($this->names) );

        foreach( $this->names as $name )
            $this->raw_msg->write_raw_string( $name );

        $this->raw_msg->write_raw_int32( $this->file_id );
        $this->raw_msg->write_raw_int8( $this->force );

        return true;
    }
}

/**
 * Set file priority
 *
 * Opcode 51 (0x33)
 */
class COpcode_SetFilePriority extends CMessage
{
    var $opcode = 51;

    var $file_id = 0;
    var $new_priority = 0;

    function __construct( &$socket, $file_id, $new_priority )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
        $this->new_priority = $new_priority;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );
        $this->raw_msg->write_raw_int32( $this->new_priority );

        return true;
    }
}

/**
 * User identification information.
 *
 * Opcode 52 (0x34)
 */
class COpcode_PassWord extends CMessage
{
    var $opcode = 52;

    var $pass = '';
    var $user = '';

    function __construct( &$socket, $user, $pass )
    {
        parent::__construct( $socket );

        $this->user = $user;
        $this->pass = $pass;
    }

    function build()
    {
        $this->raw_msg->write_raw_string( $this->pass );
        $this->raw_msg->write_raw_string( $this->user );

        return true;
    }
}

/**
 * Close search
 *
 * Opcode 27 (0x1b) (Old opcode)
 * Opcode 53 (0x35)
 */
class COpcode_CloseSearch extends CMessage
{
    var $opcode = 53;

    var $search_id = 0;
    var $forget = false; // 0 = close search; 1 = forget (not used in opcode 27)

    function __construct( &$socket, $search_id, $forget )
    {
        parent::__construct( $socket );

        $this->search_id = $search_id;
        $this->forget = $forget ? true : false;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->search_id );
        $this->raw_msg->write_raw_int8( $this->forget );

        return true;
    }
}

/**
 * Add a Server
 *
 * Opcode 54 (0x36)
 */
class COpcode_AddServer extends CMessage
{
    var $opcode = 54;

    var $network_id = 0;
    var $ip_address = '0.0.0.0';
    var $port = 0;

    function __construct( &$socket, $network_id, $ip_address, $port )
    {
        parent::__construct( $socket );

        $this->network_id = $network_id;
        $this->ip_address = $ip_address;
        $this->port = $port;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->network_id );
        $this->raw_msg->write_raw_int32( ip2long($this->ip_address) );
        $this->raw_msg->write_raw_int16( $this->port );

        return true;
    }
}

/**
 * Message versions
 *
 * Only for protocol version 18 !!!
 *
 * Opcode 55 (0x37)
 */
class COpcode_MessageVersions extends CMessage
{
    var $opcode = 55;

    /**
     * Each element is an array itself with following keys:
     *
     * - \c 'opcode'  => message opcode,
     * - \c 'fromgui' => if the message originates at the GUI (true) or at
     *                   the server (false),
     * - \c 'version' => protocol version to use
     */
    var $messages = array();

    function __construct( &$socket, $messages )
    {
        parent::__construct( $socket );

        $this->messages = $messages;
    }

    function build()
    {
        $this->raw_msg->write_raw_int16( count($this->messages) );

        foreach( $this->messages as $message )
        {
            $this->raw_msg->write_raw_int32( $message['opcode'] );
            $this->raw_msg->write_raw_int8( $message['fromgui'] );
            $this->raw_msg->write_raw_int32( $message['version'] );
        }

        return true;
    }
}

/**
 * Rename a file
 *
 * Opcode 56 (0x38)
 */
class COpcode_RenameFile extends CMessage
{
    var $opcode = 56;

    var $file_id = 0;
    var $new_name = '';

    function __construct( &$socket, $file_id, $new_name )
    {
        parent::__construct( $socket );

        $this->file_id = $file_id;
        $this->new_name = $new_name;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->file_id );
        $this->raw_msg->write_raw_string( $this->new_name );

        return true;
    }
}

/**
 * Get uploaders
 *
 * Opcode 57 (0x39)
 */
class COpcode_GetUploaders extends CMessage
{
    var $opcode = 57;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Get pending
 *
 * Opcode 58 (0x3a)
 */
class COpcode_GetPending extends CMessage
{
    var $opcode = 58;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Get searches
 *
 * Opcode 59 (0x3b)
 */
class COpcode_GetSearches extends CMessage
{
    var $opcode = 59;

    function build()
    {
        // No payload
        return true;
    }
}

/**
 * Get search
 *
 * Opcode 60 (0x3c)
 */
class COpcode_GetSearch extends CMessage
{
    var $opcode = 60;

    var $search_id = 0;

    function __construct( &$socket, $search_id )
    {
        parent::__construct( $socket );

        $this->search_id = $search_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->search_id );

        return true;
    }
}

// Opcode 61 == Opcode 31 (Connect client)

/**
 * Disconnect from client
 *
 * Opcode 62
 */
class COpcode_DisconnectClient extends CMessage
{
    var $opcode = 62;

    var $client_id = 0;

    function __construct( &$socket, $client_id )
    {
        parent::__construct( $socket );

        $this->client_id = $client_id;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->client_id );

        return true;
    }
}

/**
 * Network message
 *
 * Opcode 63
 */
class COpcode_NetworkMessage extends CMessage
{
    var $opcode = 63;

    var $network_id = 0;
    var $message = '';

    function __construct( &$socket, $network_id, $message )
    {
        parent::__construct( $socket );

        $this->network_id = $network_id;
        $this->message = $message;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->network_id );
        $this->raw_msg->write_raw_string( $this->message );

        return true;
    }
}

/**
 * Interested in sources
 *
 * Opcode 64
 */
class COpcode_InterestedInSources extends CMessage
{
    var $opcode = 64;

    var $interested = false;

    function __construct( &$socket, $interested )
    {
        parent::__construct( $socket );

        $this->interested = $interested ? true : false;
    }

    function build()
    {
        $this->raw_msg->write_raw_int8( $this->interested );

        return true;
    }
}
/**
 * Get version
 *
 * Opcode 65
 */
class COpcode_GetVersion extends CMessage
{
    var $opcode = 65;

    function build()
    {
        // No payload
        return true;
    }
}

// 66 = ServerRename( int32, string )
// 67 = ServerSetPreferred( int32, int8 )

/**
 * Ask for stats messages
 *
 * Opcode 68
 */
class COpcode_GetStats extends CMessage
{
    var $opcode = 68;

    var $identifier = 0; ///< ???

    function __construct( &$socket, $identifier )
    {
        parent::__construct( $socket );

        $this->identifier = $identifier;
    }

    function build()
    {
        $this->raw_msg->write_raw_int32( $this->identifier );

        return true;
    }
}
