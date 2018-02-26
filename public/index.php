<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

$app = require __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/
//阿里app_key
define('APP_KEY','LTAIaUJOa5mb1Hxv');

//阿里app_secret
define('APP_SECRET','djy2eoRTD5Cv6Ft0iszNQAVi9Pcho5');
//
define('RSAPRIVATEKEY','MIIEpAIBAAKCAQEAttR7YvYcqMZ30QEOE6gq8oJFl5DHHlGlj7ydihyPoSx5mSk8YNYuQHPX2z+22xJmN/MLE/9zkIQsb0VoPhh/TIfQWMW4hHqsnlSmdt1Putk+OGsycEjTJjCPu32U8pOqK9PnAHt0tflxXIGaPBtmbVe6T2XyRsnERTE44iOkslizXU9H4imtuZjbOsyaiKyHUBP4sNngvJH1pPtb16qkd6Exi16P3ctzfyWp3FB6p4OZ1chcBKNjObXW2v0iTIjI5MGQDW0OK2LqmRL7+t1PTCuEBJIkc9GNIuC2ePqAXuNi1vtVBClCpFHGsnpp3OhmwS1AwsOQZpnXYQdKK1zdfQIDAQABAoIBAQCaA9UNQJZT9xzoDCN3m6rSL7vAOk4C1HTL6PAtcHHuLDEjPQGH8eV0liG4qKu5UH6bkzo51m/bxfxIoAd4h9p09dQldCpEL7NKjbTNXRHVLTyk+mi6/h4hhbcPEnNvBcXMte1bEqT2xvMgIm1zRQG8CrMAP7kguMeGIjjxB2Wudmw6N00WeNFhhnnJa6tmmKIRmb7r6lcGeN7OSDar/hjZ6SZd/6O2jq1veE9E11xuFx4ojyrLXF7FrMTkU5i1EStMt3cRmMQ/pAOPo3LY0fqFadEIUnECiCHUNMgqVljtPJFvMHn+bH5dR7JLzd/ZPIKl8ZFnmYunxKTE+MyUyPUBAoGBAO3+2EeJ6kucGSYQ1uHJo2f+uf7apiBHeFMPiqU68rtiDWecradruvR8+0XotfxssdapF/bLnxl9+AzfPjMinI+XKtr6VVxpSNOt0jSlTVK4EKk5lUJIm0WB18vIqb8QI9QGNHSNsfTZInmLMAU+NiBhGYAlelt4hg/KVzpYjDwpAoGBAMSpRXG4fAk44F1bpK51yg5RQnRgOxVK2SM8owecSclw+QXPNKpRaKesoet6C8dSIuiuITBi3XBERozhjcIzQyNjnGcOfk/lmgTfkobg6PbxWCUDIpqf37blq0QYJ304PhlHGr7b20rt12w5SAn/kjS0yPhAyrGtkNi4Ml+EXkE1AoGBAIP7aAWEPiJgFrI9dG0SXUQ2xv7GUopkGB436DNpVWMFJVnoUYUfATbEeLR0MTgjLxxT+a4rjbedXAoHY38IGrqmomV9ngj5eiGpq4D2isLswJCYFmYDzdfmAb5JMZl7YeMa7cwVKDMYlz3AKok9ztZ2AqZGImzHkdD/7Q+2O07BAoGABYlSaCoua8ALKPWshDDSuGZ3PXl8EFRkGobZfBxdQ2uHxy8XJ3hVPuswP8XW0Qyx6OUGECuoWkHBzrOG3yG8USG7xHb2/V7UYDC/Gkb4qAMRXTFiZwH7NVXv5WHcsrzDmsiSqy7bVJevid9u9MEaJ5uzULHaQvhE7KT63g+G68UCgYBkDZH8mnGD9TE/wyK4PaXN0dAOMTsoEdZU2D1Sws9ZYuF66YAWNCxNo9iRgdHWnhfB+BIeH557XdQCS8o+niiUq4Hj8x0ce8Zp5sS8IlMqNAL4ewABi/pfWwF7gdtiT5FCD/JOXma2zSzGuRV6OZxVEbB0DoKu4BfHWTF8f0PeAg==');
//
define('ALIPAYRSAPUBLICKEY','MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgYJrttisIMRPWxOn87zPNbWD59r5ZUmn4okkokrX99s6hH6Umf1EwzXMg0q8pyY/Yv3QO3Ik3l1w7BctCstGid0sLEOXgaNA5lnmmfNqd0mqAcQVsE6BvezlBH2oEFmkgwk4dsX0CX2xxzBqh4kVphpBnKrzqBJ4JKxN57d0HSQknLHNvWcbkcfcq9fOMw9qtlg1S0lxrn1NnSF1tyTvYPTtDoT6qRJNEtsBxcBQFDeT7OqmrDXJhgsehwIh4M0+VwfxnN3dvaUK5TwvxLLOKTgvtvNv9Vt8wxjcN/E7WzZMW1UYdm/eyEg/r/hwPD2Q2WYsX7uRUpP7vHJ6ELMLdwIDAQAB');
//阿里pay的app_id
define('ALIPAY_APP_ID','2018011801946805');
$app->run();
