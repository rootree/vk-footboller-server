# vk-footboller-server
Application server for VK Footboller. This server is API for the VK Footboller client.

In most cases there are controllers and models, and several cron scripts. 

#### Exchange example

Request:

```
{
    "checksum":"1ebd001b63489068b35210c0e594db33",
    "referrerId":"0",
    "command":"friend_team",
    "authKey":"db67672ffed2c96e1417bb4ddcf15222",
    "params":{
        "19846487":{}
    },
    "id":"4778426"
}
```


Responce in this case:

```
{   
    "isOk":true,
    "command":"friend_team",
    "response":{
        "teams":[],
        "friends":[]
    }
}
```