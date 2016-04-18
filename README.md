CAP-API - A wrapper for the CAP crawling project
================================================


###Cassandra
The project uses Cassandra as the default database. There are few different packages available, but I used the (Datastax PHP driver| 

####Dependencies, Ubuntu install
```
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