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
