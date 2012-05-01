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
 * Opcodes sent FROM the server.
 */
$OPCODE_RECV = array(
     0 => 'CoreProtocol',
     1 => 'OptionsInfo',
     3 => 'DefineSearches',
     4 => 'ResultInfo',
     5 => 'SearchResult',
     9 => 'FileUpdateAvailability',
    10 => 'FileAddSource',
    12 => 'ServerUser',
    13 => 'ServerState',
    15 => 'ClientInfo',
    16 => 'ClientState',
    19 => 'ConsoleMessage',
    20 => 'NetworkInfo',
    21 => 'UserInfo',
    22 => 'RoomInfo',
    23 => 'RoomMessage',
    24 => 'RoomAddUser',
    26 => 'ServerInfo',
    27 => 'MessageFromClient',
    28 => 'ConnectedServers',
    31 => 'RoomInfo',
    34 => 'SharedFileUpload',
    35 => 'SharedFileUnshared',
    36 => 'AddSectionOption',
    38 => 'AddPluginOption',
    46 => 'FileDownloadUpdate',
    47 => 'BadPassword',
    48 => 'SharedFileInfo',
    49 => 'ClientStats',
    50 => 'FileRemoveSource',
    51 => 'CleanTables',
    52 => 'FileInfo',
    53 => 'DownloadingFiles',
    54 => 'DownloadedFiles',
    55 => 'Uploaders',
    56 => 'Pending',
    57 => 'Search',
    58 => 'Version',
    59 => 'Stats'
);

/**
 * Get opcode name from its value.
 *
 * @param $value Opcode sent by the server.
 *
 * @return Corresponding opcode name.
 */
function opcode_name($value)
{
    global $OPCODE_RECV;

    if( isset( $OPCODE_RECV[$value] ) )
        return $OPCODE_RECV[$value];

    return false;
}

/**
 * Opcodes sent TO the server.
 */
$OPCODE_SENT = array(
     0 => 'ProtocolVersion',
     1 => 'ConnectMore',
     2 => 'CleanOldServers',
     3 => 'KillServer',
     4 => 'ExtendedSearch',
     8 => 'DlLink',
     9 => 'RemoveServer',
    10 => 'SaveOptions',
    11 => 'RemoveDownload',
    12 => 'GetServerUsers',
    13 => 'SaveFileAs',
    14 => 'AddClientFriend',
    15 => 'AddUserFriend',
    16 => 'RemoveFriend',
    17 => 'RemoveAllFriends',
    18 => 'FindFriend',
    19 => 'ViewUsers',
    20 => 'ConnectAll',
    21 => 'ConnectServer',
    22 => 'DisconnectServer',
    23 => 'SwitchDownload',
    24 => 'VerifyAllChunks',
    25 => 'QueryFormat',
    26 => 'ModifyMp3Tags',
    27 => 'CloseSearch',
    28 => 'SetOption',
    29 => 'ConsoleCommand',
    30 => 'Preview',
    31 => 'ConnectFriend',
    32 => 'GetServerUsers',
    33 => 'GetClientFiles',
    34 => 'GetFileLocations',
    35 => 'GetServerInfo',
    36 => 'GetClientInfo',
    37 => 'GetFileInfo',
    38 => 'GetUserInfo',
    40 => 'EnableNetwork',
    41 => 'BrowseUser',
    42 => 'SearchQuery',
    43 => 'MessageToClient',
    44 => 'GetConnectedServers',
    45 => 'GetDownloadingFiles',
    46 => 'GetDownloadedFiles',
    47 => 'GuiExtensions',
    49 => 'RefreshUploadStats',
    50 => 'Download',
    51 => 'SetFilePriority',
    52 => 'PassWord',
    53 => 'CloseSearch',
    54 => 'AddServer',
    55 => 'MessageVersions',
    56 => 'RenameFile',
    57 => 'GetUploaders',
    58 => 'GetPending',
    59 => 'GetSearches',
    60 => 'GetSearch',
    61 => 'ConnectClient',
    62 => 'DisconnectClient',
    63 => 'NetworkMessage',
    64 => 'InterestedInSources',
    65 => 'GetVersion',
    68 => 'GetStats'
);

/**
 * Get opcode value from its name.
 *
 * @param $opcode_name opcode name you want to send.
 *
 * @return Corresponding opcode value.
 */
function opcode_value($opcode_name)
{
    global $OPCODE_SENT;

    foreach( $OPCODE_SENT as $opcode => $name )
        if( $opcode_name == $name ) return $opcode;
}
