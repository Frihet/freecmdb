insert into ci_property (name, value) values ('chart.maxDepth', '');
insert into ci_property (name, value) values ('chart.maxItems', '');
insert into ci_property (name, value) values ('pager.itemsPerPage', '');
insert into ci_property (name, value) values ('core.baseURL', 'http://__domain__/');
insert into ci_property (name, value) values ('core.baseUrl', '/FreeCMDB/');
insert into ci_property (name, value) values ('plugin.drilldown.root', '0');
insert into ci_property (name, value) values ('core.dateTimeFormat', 'd.m.Y H:i');
insert into ci_property (name, value) values ('core.dateFormat', 'd.m.Y');
insert into ci_property (name, value) values ('core.locale', 'nb_NO.utf8');
insert into ci_property (name, value) values ('plugin.breadcrumb.root_url', 'CMDB');
insert into ci_property (name, value) values ('plugin.breadcrumb.root_title', 'http://__domain__/FreeCMDB/plugins/drilldown/drilldown');

insert into ci_event (event_name, class_name) values ('Sidebar', 'drilldownPlugin');
insert into ci_event (event_name, class_name) values ('Startup', 'drilldownPlugin');
insert into ci_event (event_name, class_name) values ('Shutdown', 'breadcrumbPlugin');

insert into ci_plugin (name, description, version, author) values ('drilldown', 'Plugin for structured navigation through the CMDB', '1.0', 'Axel Liljencrantz');
insert into ci_plugin (name, description, version, author) values ('breadcrumb', 'Breadcrumbs', '0,01', 'Egil Möller');

insert into ci (id, ci_type_id) select 0, ci_type.id from ci_type where ci_type.name = 'Service';
insert into ci_column (ci_id, ci_column_type_id, value) select 0, ci_column_type.id, 'Root' from ci_column_type where ci_column_type.name = 'Name';

