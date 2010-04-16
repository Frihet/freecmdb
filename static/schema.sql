drop view if exists ci_view;
drop view if exists ci_column_view;

drop index if exists ci_dependency_idx;

drop table if exists ci_graph_cache;
drop table if exists ci_plugin;
drop table if exists ci_property;
drop table if exists ci_log;
drop table if exists ci_dependency;
drop table if exists ci_dependency_type;
drop table if exists ci_column;
drop table if exists ci;
drop table if exists ci_column_list;
drop table if exists ci_column_type;
drop table if exists ci_type;
drop table if exists ci_session;
drop table if exists ci_user;
drop table if exists ci_event;

create table ci_user
(
        id serial not null primary key, 
        username varchar(64) not null unique, 
        fullname varchar(64) not null, 
        password varchar(64) not null,
        email varchar(64),
        deleted boolean not null default false,
	can_view boolean not null default false,
	can_edit boolean not null default false,
	can_admin boolean not null default false
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
	ci_type_id int references ci_type(id),
	prefix varchar(64) not null default '',
	suffix varchar(64) not null default '',
	pattern varchar(64) not null default '',
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

create table ci_dependency_type
(
	id serial not null primary key,
	name varchar(64) not null,
	reverse_name varchar(64) not null,
	color varchar(16) not null,
	deleted boolean not null default false
);

create table ci_dependency
(
	id serial not null primary key,
	ci_id int not null references ci(id),
	dependency_id int not null references ci(id),
	dependency_type_id int not null references ci_dependency_type(id)
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
	dependency_type_id int references ci_dependency_type(id),
	user_id int not null references ci_user(id)
);

create table ci_property
(
	id serial not null primary key,
	name varchar(64) not null unique,
	value varchar(2048) not null
);

create table ci_event
(
	id serial not null primary key,
	event_name varchar(32) not null,
	class_name varchar(64) not null
);

create table ci_plugin
(
	id serial not null primary key,
	name varchar(32) not null,
	description varchar(2048) not null,
	version varchar(16) not null,
	author varchar(256) not null
);

create table ci_graph_cache
(
	id serial not null primary key,
	key varchar(40) not null unique,
	value varchar(32000) not null
);

create unique index ci_dependency_idx on ci_dependency (ci_id, dependency_id);
create index ci_session_user_idx on ci_session (user_id);
create index ci_column_ci_idx on ci_column (ci_id);
create index ci_log_ci_idx on ci_log (ci_id);

create view ci_view as
select c.*, ct.name as type_name 
from ci as c
join ci_type as ct on
c.ci_type_id = ct.id;

create view ci_column_view as
select c.id, cct.name, cct.id as column_type_id, cc.value
from ci c
join ci_column_type cct
on cct.ci_type_id is null or c.ci_type_id = cct.ci_type_id
left join ci_column cc
on c.id = cc.ci_id and cc.ci_column_type_id = cct.id
where cct.deleted=false;

insert into ci_type (name,shape) values ('Server','octagon');
insert into ci_type (name,shape) values ('Virtual Server','doubleoctagon');
insert into ci_type (name,shape) values ('Service','ellipse');
insert into ci_type (name,shape) values ('Project','house');
insert into ci_type (name,shape) values ('Firewall','triangle');
insert into ci_type (name,shape) values ('Router','triangle');

insert into ci_column_type (name,type) values ('Uri',0);
insert into ci_column_type (name,type) values ('Description',1);
insert into ci_column_type (name,type) values ('Links',0);
insert into ci_column_type (name,type) values ('Name',0);
insert into ci_column_type (name,type) values ('External information',4);

insert into ci_dependency_type(name, reverse_name, color) 
values ('Depends on','Depended on by', 'black');

insert into ci_dependency_type(name, reverse_name, color) 
values ('Owned by','Owner of', 'red');

insert into ci_dependency_type(name, reverse_name, color) 
values ('Responsibility of','Responsible for', 'red');

insert into ci_dependency_type(name, reverse_name, color) 
values ('Redundant', '', 'green');

insert into ci_dependency_type(name, reverse_name, color) 
values ('Falls back on', 'Fallback for', 'green');

insert into ci_property (name, value) 
select 'ciColumn.default', id 
from ci_column_type 
where name='Name';

insert into ci_property (name, value) 
select 'ciDependency.default', id 
from ci_dependency_type 
where name='Depends on';

insert into ci_user (username, fullname, password, email, can_view, can_edit, can_admin) values ('admin','Administrator','','axel.liljencrantz@freecode.no',true, true, true);
