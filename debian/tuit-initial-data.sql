insert into ci_property (name, value) values ('plugin.breadcrumb.root', '<a href="/tuit">Hjem</a> &gt; <a href="/FreeCMDB/plugins/drilldown/drilldown">CMDB</a>');
insert into ci_property (name, value) values ('plugin.breadcrumb.admin_root', '<a href="/tuit">Hjem</a> &gt; <a href="/tuit/admin">Administrasjon</a> &gt; <a href="/FreeCMDB/admin">CMDB</a>');

insert into ci_property (name, value) values ('loginTuit.editGroup', '');
insert into ci_property (name, value) values ('loginTuit.adminGroup', '');
insert into ci_property (name, value) values ('loginTuit.viewGroup', '');
insert into ci_property (name, value) values ('plugin.tuit.DSN', 'pgsql:dbname=__dbc_dbname__;host=__dbc_dbserver;user=__dbc_dbuser__;password=__dbc_dbpass__');
insert into ci_property (name, value) values ('plugin.tuit.closedId', '2');
insert into ci_property (name, value) values ('tuit.enabled', '1');

insert into ci_plugin (name, description, version, author) values ('loginTuit', 'Single signon via tuit', '1.0', 'Axel Liljencrantz');
insert into ci_plugin (name, description, version, author) values ('tuit', 'Integration for the Tuit ticket handling system', '1.0', 'Axel Liljencrantz, FreeCode AS');

insert into ci_event (event_name, class_name) values ('Startup', 'loginTuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerView', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerRemove', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerSaveAll', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerUpdateField', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerCopy', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerRevert', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiListControllerView', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('Startup', 'tuitPlugin');
