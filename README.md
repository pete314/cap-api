CAP crawler API
===
RESTful API for cap crawler.

**Introduction**
----
Cap crawler is a simple python crawler with multi threaded execution and shared execution queue, with cli execution options. This project is a wrapper around cap crawler. 

**Features**
----
 - Private/public key or password based authentication<br>
 - Hmac(sha256) based request authentication (similar to [AWS authentication](http://docs.aws.amazon.com/AmazonS3/latest/dev/RESTAuthentication.html)) <br>
 - Chained validation(Auth<>identity<>endpoint<>request etc.)<br>
 - Json/Csv/zip response options<br>
 - Stateless & scalable
 - Custom error handling

----------


**Dependencies**
----
The API is written in php with [Zend Framework 2](http://framework.zend.com/). The framework  dependencies can be installed with composer.
```php
cd /path/to/repo
php composer.phar update -o --no-dev
```
Modules can be installed separately, for more details visit the [official Zend documentation.](http://framework.zend.com/downloads/composer)

I suggest to use Opcache for better performance or use the [ClassMapAutoloader feature provided by Zend.](http://framework.zend.com/manual/current/en/modules/zend.loader.class-map-autoloader.html)


###Database
The project uses Cassandra as the default database. There are few different packages available, but I used the [Datastax PHP driver](https://github.com/datastax/php-driver) , the documentation is available [here](http://datastax.github.io/php-driver/).


####Cassandra Dependencies, Ubuntu install
The Datastax driver can be installed trough pecl, and requires additional dependencies as it is a wrapper around the datastax c++ driver.

```bash
sudo apt-get update
sudo apt-get install -y g++ git make cmake clang libssl-dev libgmp-dev openssl libpcre3-dev
sudo apt-get update
sudo apt-get install libc6
sudo apt-get -f install
sudo apt-get update
cd /tmp
sudo curl http://downloads.datastax.com/cpp-driver/ubuntu/14.04/libuv_1.7.5-1_amd64.deb > libuv_1.7.5-1_amd64.deb
sudo curl http://downloads.datastax.com/cpp-driver/ubuntu/14.04/libuv-dev_1.7.5-1_amd64.deb > libuv-dev_1.7.5-1_amd64.deb
sudo curl http://downloads.datastax.com/cpp-driver/ubuntu/14.04/cassandra-cpp-driver-dev_2.3.0-1_amd64.deb > cassandra-cpp-driver-dev_2.3.0-1_amd64.deb	
sudo curl http://downloads.datastax.com/cpp-driver/ubuntu/14.04/cassandra-cpp-driver_2.3.0-1_amd64.deb > cassandra-cpp-driver_2.3.0-1_amd64.deb
sudo dpkg -i libuv_1.7.5-1_amd64.deb
sudo dpkg -i libuv-dev_1.7.5-1_amd64.deb
sudo dpkg -i cassandra-cpp-driver_2.3.0-1_amd64.deb
sudo dpkg -i cassandra-cpp-driver-dev_2.3.0-1_amd64.deb	
sudo apt-get update
sudo pecl install cassandra
sudo apt-get update
```
Don' forget to add the extension to php.ini("extension=cassandra.so")

For Zend server users on Linux I would suggest to install in both places for better IDE integration(Netbeans/PhpStorm) and enable the extension in both install locations.

**Database structures**
--------------------

**Cassandra**
The setup may differ from the use case. I suggest to run the following script trough cqlsh.
```Sql
-- Single node configuration
-- Create keyspace for the project, this is single node setup use NetworkTopologyStrategy (if) production
create keyspace CapData with replication = {'class': 'SimpleStrategy', 'replication_factor' : 1};
use CapData;
-- Create users table, used for keep track of crawl jobs
create table users (user_id varchar primary key, email varchar, password text, first_name varchar, last_name varchar, updated timestamp, created timestamp);
create index on CapData.users (public_key);
-- Create request_log table, used for log dump
create table request_log (id varchar primary key, redirect_url varchar, remote_addr varchar, request_path varchar, request_auth text, created timestamp);
-- Create crawl_job table, used for keep track of crawl jobs
create table crawl_job (job_id varchar primary key, user_id varchar, status varchar, job_type varchar, startin_params text, has_depth varchar, recurrance varchar, created timestamp, started timestamp, finished timestamp);
create index on CapData.crawl_job (user_id);
```

###Available Modules:
**Common**: holds core functions, classes used by other modules like Adapters, Listeners etc.<br>
**User**: User based functions and endpoints<br>
**Crawl**: Wrapper around the crawling jobs<br>


**Endpoint usage examples**
--- 
####User registration
The endpoint does not require any authorization.
*/api/user/register*
Request
```Bash
curl -v -X POST -H "Content-Type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW" -F "email=john.doe@example.com" -F "password=password" -F "first_name=John" -F "last_name=Doe" "https://host/api/user/register"
```
Response
```Json
{
  "success": true,
  "data": {
    "user_id": "41e33a48e25abb86bc5157bf3d28c786",
    "public_key": "834356a16d7f970c5e540d438ee48ddb",
    "private_key": "8cd1cc01c986c6bfe5c633a6f3a7f5925754bbcb5084f71219eb2959b580bdc1"
  },
  "errors": []
}
```
Note: in the background an email is sent with the credentials to the email address supplied in request. 

####Single job report
The endpoint does require authorization, based on the public/private keys and payload. In this case the payload is the endpoint URI
*/api/crawl/job/report/{USER_ID}/{JOB_ID}*
Request
```Bash
curl -v -X GET -H "Authorization: CAPMAC {USER_PUBLIC_KEY}:{HMAC_SHA256_DIGEST}" "https://host/api/crawl/job/report/{USER_ID}/{JOB_ID}"
```
Response
```Json
{
  "success": true,
  "data": {
      "job_id": "77cec83335e1bc59697de6a837256395a479422546b1e8451461431589",
      "created": {
        "type": {
          "name": "timestamp"
        },
        "seconds": 1461431589,
        "microseconds": 212000
      },
      "finished": {
        "type": {
          "name": "timestamp"
        },
        "seconds": 1461435199,
        "microseconds": 730000
      },
      "has_depth": "N/A",
      "job_type": "link-crawl",
      "recurrance": "daily:1",
      "started": {
        "type": {
          "name": "timestamp"
        },
        "seconds": 1461435189,
        "microseconds": 403000
      },
      "startin_params": "site-root:http://www.gmit.ie,start-at-page:http://www.gmit.ie/international/science-without-borders",
      "status": "READY",
      "user_id": "350b2bfcc9129e5b3aa49165cb373c2f",
      "gmt": "Sat, 23 Apr 2016 17:13:09 GMT+0000"
  },
  "errors": null
}
```
Note: the response holds details about job, results for the job are available trough different endpoints.
 
###Error response example
If something goes wrong either within the application or by user error(wrong parameters/security issues etc.) a response in the following structure is returned with error message related to the issue.
```Json
{
  "success": false,
  "data": false,
  "errors": "Request authorization failed, check keys/payload"
}
```
Note: the status code is set based on the error in the above case '403 - Forbidden'

Notes
-----

**PUT/POST/GET/HEAD** HTTP methods are available on some endpoints.<br/>
**Hmac digest** is calculated on the payload, which is either the body of the request or the path if body is not available(GET|HEAD). The path is only used to make scaling on multiple hosts easier, so the subdomain or different IP does not make a difference in the generation/calculation, which makes load balancing easier among others. <br/>
The **application can't run** without the config files set properly in *config/autoload/*. A *.dist version is supplied for each, with the required structure. Remove the .**dist** and fill the values accordingly.<br/>
**Error/logs generated** by the application are stored in **data/log/** folder with *date-LOG-NAME.log*. 
**Error handling** is managed on two levels, with listener attached to render events which can handle all errors happening within the framework. The second layer is catching the language level errors where recovery is not possible. With this in place a controlled response can be generated even if errors occurs.


##Known issues
The zipped content after extraction contains binary not html.<br/>
Currently link crawling is only accessible without "focus content" or "xpath" options, which will collect the entire html content. This is a limitation of the crawler.


##References
[AWS authentication](http://docs.aws.amazon.com/AmazonS3/latest/dev/RESTAuthentication.html)<br>
[Hash-based message authentication code (Hmac)](https://en.wikipedia.org/wiki/Hash-based_message_authentication_code)<br>
[Zend Framework 2](http://framework.zend.com/)<br>
[Zend moduls trough composer - documentation.](http://framework.zend.com/downloads/composer)<br>
[ClassMapAutoloader feature provided by Zend.](http://framework.zend.com/manual/current/en/modules/zend.loader.class-map-autoloader.html)<br>
[Datastax PHP driver](https://github.com/datastax/php-driver)<br>
[CAP crawler](https://github.com/pete314/cap-crawler)


##Disclaimer

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
