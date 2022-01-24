# Lovat_Api

## Description
WordPress WooCommerce REST API Lovat Api

## Установка плагина

Download the extension as a ZIP file from this repository under the wp-content/plugins directory

Next, activate the plugin and get the access key in the settings.

Use request authorization

```
Authorization: Bearer Token
```
Params: `from` `to` `p`

`from` `to` -> date format

`p` -> integer (pagination), default = 1

URL request 
```
/wp-json/v1/orders?from=date&to=date&p=integer
```
 
Request URL example

```
http://localhost/wp-json/v1/orders?from=15.08.2020&to=30.08.2020&p=1
```

The search is carried out according to such statuses as `completed` or `refunded`