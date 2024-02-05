<?php

namespace App\Services\Terminal\Command;

enum Command
{
    const TRANSFER_KEY  = "TransferKey";
    const SET_UP_SERVER = "SetUpServer";
    const DELETE_SERVER = "DeleteServer";
    const CREATE_INBOUND = "CreateInbound";
    const UPDATE_INBOUND = "UpdateInbound";
    const DELETE_INBOUND = "DeleteInbound";
    const BANDWIDTH      = "Bandwidth";
}
