insert into ci_property (name, value) values ('chart.maxDepth', '');
insert into ci_property (name, value) values ('chart.maxItems', '');
insert into ci_property (name, value) values ('pager.itemsPerPage', '');
insert into ci_property (name, value) values ('core.baseURL', 'http://$(hostname -f)/');
insert into ci_property (name, value) values ('core.baseUrl', '/FreeCMDB/');
insert into ci_property (name, value) values ('plugin.drilldown.root', '0');
insert into ci_property (name, value) values ('core.dateTimeFormat', 'd.m.Y H:i');
insert into ci_property (name, value) values ('core.dateFormat', 'd.m.Y');
insert into ci_property (name, value) values ('core.locale', 'nb_NO.utf8');
insert into ci_property (name, value) values ('loginTuit.editGroup', '');
insert into ci_property (name, value) values ('loginTuit.adminGroup', '');
insert into ci_property (name, value) values ('loginTuit.viewGroup', '');
insert into ci_property (name, value) values ('plugin.tuit.DSN', 'pgsql:dbname=tuit;host=127.0.0.1;user=tuit;password=${TUIT_DB_PW}');
insert into ci_property (name, value) values ('plugin.tuit.closedId', '2');
insert into ci_property (name, value) values ('tuit.enabled', '1');
insert into ci_property (name, value) values ('plugin.breadcrumb.root_url', 'CMDB');
insert into ci_property (name, value) values ('plugin.breadcrumb.root_title', 'http://$(hostname -f)/FreeCMDB/plugins/drilldown/drilldown');
insert into ci_property (name, value) values ('plugin.breadcrumb.root', '<a href="/tuit">Hjem</a> &gt; <a href="/FreeCMDB/plugins/drilldown/drilldown">CMDB</a>');
insert into ci_property (name, value) values ('plugin.breadcrumb.admin_root', '<a href="/tuit">Hjem</a> &gt; <a href="/tuit/admin">Administrasjon</a> &gt; <a href="/FreeCMDB/admin">CMDB</a>');

insert into ci_event (event_name, class_name) values ('Sidebar', 'drilldownPlugin');
insert into ci_event (event_name, class_name) values ('Startup', 'loginTuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerView', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerRemove', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerSaveAll', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerUpdateField', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerCopy', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiControllerRevert', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('CiListControllerView', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('Startup', 'drilldownPlugin');
insert into ci_event (event_name, class_name) values ('Startup', 'tuitPlugin');
insert into ci_event (event_name, class_name) values ('Shutdown', 'breadcrumbPlugin');

insert into ci_plugin (name, description, version, author) values ('drilldown', 'Plugin for structured navigation through the CMDB', '1.0', 'Axel Liljencrantz');
insert into ci_plugin (name, description, version, author) values ('loginTuit', 'Single signon via tuit', '1.0', 'Axel Liljencrantz');
insert into ci_plugin (name, description, version, author) values ('tuit', 'Integration for the Tuit ticket handling system', '1.0', 'Axel Liljencrantz, FreeCode AS');
insert into ci_plugin (name, description, version, author) values ('breadcrumb', 'Breadcrumbs', '0,01', 'Egil Möller');

insert into ci (id, ci_type_id) select 0, ci_type.id from ci_type where ci_type.name = 'Service';
insert into ci_column (ci_id, ci_column_type_id, value) select 0, ci_column_type.id, 'Root' from ci_column_type where ci_column_type.name = 'Name';

