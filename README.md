Paymuna Payment Module for Prestashop
==============================================

## Prerequisites
Latest Version Tested:
- Prestashop v1.7.x

You must have a Paymuna account to use this plugin. Having an account is free, so [go ahead and signup for a Paymuna account](http://paymuna.com/).

## Server Requirements
- [Prestashop](https://www.prestashop.com/en/download) <= 1.7.x (Older versions will work but this plugin wasn't tested to these older versions.)
- [PHP](http://php.net/) >= 5.6 (This plugin is tested with 7.0 version)
- cURL (Once you have PHP installed, it will be basically included on it.)

## Installation
### Upload
Once you have downloaded the zip file containing the Paymuna module, head over to your **Prestashop Back Office > Modules > Modules & Services > Upload a Module**.

![Upload Module](https://i.imgur.com/1HNfEYB.png)

A file dialog will show and select the downloaded zip file and upload it.

![Uploading](https://i.imgur.com/GY4bB5m.png)

To verify that the plugin was successfully installed, go to **Prestashop Back Office > Modules > Modules & Services > Installed Modules** and the Paymuna plugin should show up there.

![Activate Success](https://i.imgur.com/xPHlfc3.png)

## Configuration

Before you move on configuring Paymuna make sure you already have a Checkout Template in order for you to have an **API Token**, **API Secret**, and **Checkout Reference**. If you don't have the following, head over to [Paymuna](http://paymuna.com) to generated those.

Once you have the necessary credentials, head over to **Prestashop Back Office > Modules > Modules & Services > Installed Modules > Paymuna > Configure** and start configuring your Paymuna Payment **Credentials**.
 
![Configuration](https://i.imgur.com/wCOnENL.png)

Once everything is setup, save the changes and Paymuna should be used as your **Checkout**.