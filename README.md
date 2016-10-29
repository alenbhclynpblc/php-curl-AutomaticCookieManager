# php-curl-AutomaticCookieManager
#### Simple PHP-Curl request response class for automatic cookie & refferer tracking without any development. 

## Examples And Results
PHP Code
```php
	$request = new Request();
	$request->head('https://www.aaa.com/index');

	$request = new Request();
	$request->xmlHttpRequest();
	$request->setContentType('application/x-www-form-urlencoded; charset=UTF-8');
	$request->post('https://www.aaa.com/login', [
		'username' => 'test',
		'password' => 'test'
	]);
	$request->setCookie('NSC_IUUQT-XXX.USFOEZPM.DPN', 'changed');

	$request = new Request();
	$request->get('https://www.aaa.com/index');
```

Network Traffic
```
> HEAD /index HTTP/1.1
> Host: www.aaa.com
> Accept: */*

< HTTP/1.1 200 OK
< Cache-Control: private
< Content-Length: 514645
< Content-Type: text/html; charset=utf-8
< Date: Sat, 29 Oct 2016 15:16:36 GMT
< Set-Cookie: NSC_IUUQT-XXX.USFOEZPM.DPN=ffffffff0908146445525d5f4f58455e445a4a42378b;path=/


> POST /login HTTP/1.1
> Host: www.aaa.com
> Accept: */*
> Cookie: NSC_IUUQT-XXX.USFOEZPM.DPN=ffffffff0908146445525d5f4f58455e445a4a42378b;
> X-Requested-With: XMLHttpRequest
> Content-Type: application/x-www-form-urlencoded; charset=UTF-8
> Referer: https://www.aaa.com/inedx
> Content-Length: 206

< HTTP/1.1 200 OK
< Cache-Control: private
< Content-Type: application/x-javascript; charset=utf-8
< Set-Cookie: SH=x=COOKIE_&pp=ItMm4t5HRy+PG0lVAIiFr6/xslY=&tx=mqeBMUEemR+CB9vIkC5iMa6+zgk=; domain=aaa.com; expires=Sat, 05-Nov-2016 1$
< Set-Cookie: VisitorTypeStatus=visitor; domain=aaa.com; path=/


> HEAD /index HTTP/1.1
> Host: www.aaa.com
> Accept: */*
> Cookie: SH=x=COOKIE_&pp=ItMm4t5HRy+PG0lVAIiFr6/xslY=&tx=mqeBMUEemR+CB9vIkC5iMa6+zgk=;VisitorTypeStatus=visitor;NSC_IUUQT-XXX.USFOEZPM.DPN=changed;
> Referer: https://www.aaa.com/login
...

```

