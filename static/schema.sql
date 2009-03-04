drop view ci_view;
drop view ci_column_view;

drop index ci_dependency_idx;

drop table ci_log;
drop table ci_dependency;
drop table ci_column;
drop table ci;
drop table ci_column_list;
drop table ci_column_type;
drop table ci_type;
drop table ci_session;
drop table ci_user;

create table ci_user
(
        id serial not null primary key, 
        username varchar(64) not null unique, 
        fullname varchar(64) not null, 
        password varchar(64) not null,
        email varchar(64),
        deleted boolean not null default false
);

create table ci_session 
(
        id varchar(64) not null unique, 
	expiry_time int not null, 
        user_id int not null references ci_user (id) on delete cascade
);

create table ci_type
(
	id serial not null primary key,
	name varchar(64) not null,
	shape varchar(32) not null default 'box',
	deleted boolean not null default false
);

create table ci_column_type
(
	id serial not null primary key,
	type int not null default 0,
	name varchar(64) not null,
	deleted boolean not null default false
);

create table ci_column_list (
	id serial not null primary key,
	ci_column_type_id int not null references ci_column_type(id),
	name varchar(64) not null,
	deleted boolean not null default false
);

create table ci 
(
	id serial not null primary key,
	ci_type_id int not null references ci_type(id),
	deleted boolean not null default false
);

create table ci_column
(
	id serial not null primary key,
	ci_id int not null references ci(id),
	ci_column_type_id int not null references ci_column_type(id),
	value varchar(16000) not null
);

create table ci_dependency
(
	id serial not null primary key,
	ci_id int not null references ci(id),
	dependency_id int not null references ci(id)
);

create table ci_log
(
	id serial not null primary key,
	create_time timestamp not null,
	ci_id int not null references ci(id),
	action int not null,
	type_id_old int references ci_type(id),
	column_id int references ci_column_type(id),
	column_value_old varchar(16000),
	dependency_id int references ci(id),
	user_id int not null references ci_user(id)
);

create unique index ci_dependency_idx on ci_dependency (ci_id, dependency_id);

create view ci_view as
select c.*, ct.name as type_name 
from ci as c
join ci_type as ct on
c.ci_type_id = ct.id
where c.deleted=false and ct.deleted=false;

create view ci_column_view as
select c.id, cct.name, cct.id as column_type_id, cc.value
from ci c
join ci_column_type cct
on 1=1
left join ci_column cc
on c.id = cc.ci_id and cc.ci_column_type_id = cct.id
where c.deleted=false and cct.deleted=false;

insert into ci_type (name) values ('Server');
insert into ci_type (name) values ('Virtual Server');
insert into ci_type (name) values ('Service');
insert into ci_type (name) values ('Project');
insert into ci_type (name) values ('Firewall');
insert into ci_type (name) values ('Router');

insert into ci_column_type (name) values ('Uri');
insert into ci_column_type (name) values ('Description');
insert into ci_column_type (name) values ('Service owner');
insert into ci_column_type (name) values ('Service responsible');
insert into ci_column_type (name) values ('Links');
insert into ci_column_type (name) values ('Name');

