cqlsh> create keyspace CapData with replication = {'class': 'SimpleStrategy', 'replication_factor' : 1};
cqlsh> use CapData;
cqlsh:capdata> create table users (user_id varchar primary key, email varchar, password text, first_name varchar, last_name varchar, updated timestamp, created timestamp);
