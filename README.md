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

This panel gives you the ability to create ssh accounts on your linux server to use them for tunneling.

## Features

- Add/Update/Delete inbounds
- Add multiple servers
- Activate/Deactivate inbounds
- Limit inbounds to specific bandwidth usage
- Limit concurrent connections of inbounds
- Set expire date for inbounds
- Set default settings for inbounds
- Set UDPGW port on your servers

## Supporting OS

- Ubuntu (Recommended, Tested on v20)
- CentOS

## Install / Uninstall

### Install
To install the panel, run the following command on your server:
```
wget -O main.sh https://raw.githubusercontent.com/armineslami/SSH-Accounting-Panel/master/main.sh && sudo bash main.sh
```

### Uninstall
To uninstall the panel run the following command on your server and from the menu choose uninstall option:
```
spa
```

## License

The panel is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
