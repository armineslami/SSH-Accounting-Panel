<p align="center"><a href=""><img src="https://raw.githubusercontent.com/armineslami/SSH-Accounting-Panel/master/public/img/icon-512x512.png" width="256" alt="Logo"></a></p>

<h2 align="center">
SSH Accounting Panel
<br/>
<br/>
<p>
<a href=""><img src="https://img.shields.io/badge/v1.0.0-blue?label=release" alt="Latest Version"></a>
<a href=""><img src="https://img.shields.io/badge/MIT-%2397ca00?label=licence" alt="License"></a>
</p>
</h2>



## About

This panel provides you an easy way to create ssh accounts on your servers and use them for tunneling.
You can either create accounts on your local machine or on the remote servers. The panel also provides you
a cli command called `sap` to interact with it trough the terminal.

## Features

- Add/Update/Delete inbounds
- Add multiple servers
- Activate/Deactivate inbounds
- Limit inbounds to specific bandwidth usage
- Limit concurrent connections of inbounds
- Set expire date for inbounds
- Set default settings for inbounds
- Set UDPGW port on your servers
- Set a domain and port
- Add SSL certificate to your domain

## Supported OS

- Ubuntu LTS (Tested on v20.04)

## Install / Uninstall

### Install
To install the panel, run the following command on your server:
```
wget -O sap.sh https://raw.githubusercontent.com/armineslami/SSH-Accounting-Panel/master/sap.sh && sudo bash sap.sh
```

### Uninstall
To uninstall the panel run the following command on your server and from the menu choose uninstall option:
```
sap
```

## License

The panel is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
