drop database retester;
create database retester default charset utf8;
use retester;

create table tests (
  id int auto_increment primary key,
  name varchar(255),
  created_at timestamp default current_timestamp
);
insert into tests(name) values ("IQ test");
insert into tests(name) values ("Ваш психологический тип");
