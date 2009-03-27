create table ci_rt_mapping
(
	id serial not null primary key,
	ci_id int not null references ci(id),
	rt_id int not null
);
