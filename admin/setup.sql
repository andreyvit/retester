drop database retester;
create database retester default charset utf8;
use retester;

create table tests (
  id int auto_increment not null primary key,
  name varchar(255) not null,
  created_at timestamp not null default current_timestamp,
  design_file varchar(255),
  handler_file varchar(255),
  finish_file varchar(255)
);

create table questions (
  id int auto_increment not null primary key,
  test_id int not null,
  created_at timestamp not null default current_timestamp,
  `order` int not null,
  `text` longtext not null default "",
  image_file varchar(255)
);

create table answers (
  id int auto_increment not null primary key,
  question_id int not null,
  `order` int not null,
  `text` longtext not null default "",
  points int not null default 0,
  image_file varchar(255)
);

insert into tests(name) values ("IQ-тест");
set @test_id = last_insert_id();

insert into questions(test_id, `order`, text) values (
  @test_id, 1, "Как называется приспособление для подъема воды из колодца?"
);
set @question_id = last_insert_id();

insert into answers(question_id, `order`, `text`, points) values
(@question_id, 1, "Журавль", 0),
(@question_id, 2, "Аист", 1),
(@question_id, 3, "Страус", 0),
(@question_id, 4, "Цапля", 0);

insert into questions(test_id, `order`, text) values (
  @test_id, 1, "Чью мать обещал показать американцам Хрущев?"
);
set @question_id = last_insert_id();

insert into answers(question_id, `order`, `text`, points) values
(@question_id, 1, "Кузькину", 0),
(@question_id, 2, "Чертову", 1),
(@question_id, 3, "Свою", 0),
(@question_id, 4, "Микояна", 0);

insert into tests(name) values ("Ваш психологический тип");
set @test_id = last_insert_id();
