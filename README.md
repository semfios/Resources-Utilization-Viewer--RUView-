RUView
======
RUView (pronounced like "love you" with an "r" in place of "l") is short for
Resources Utilization Viewer.

If your business is project-based, you typically have a team working with many
projects. Each project has a start and an end date, and each team member might be
working on a project for a specific period then moving to another project, or even
be working on multiple projects at the same time, dividing his/her time between
them.

It can get really tricky trying to figure out the schedule and availability of
each team member. RUView helps visualize all the resource utilization data in one
simple chart that instantly shows you who's working on what and when.

Here is a sample utilization chart for an imaginary company:
[Resource utilization chart](http://bigprof.com/appgini/sites/default/files/RUView-resources-utilization-chart.png)
[Hints you can tell from the chart](http://bigprof.com/appgini/sites/default/files/RUView-chart-explained_2.png)

License
-------
Copyright 2012 BigProf Software

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   <http://www.apache.org/licenses/LICENSE-2.0>

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

Installing RUView
-----------------
RUView requires a web server with PHP 4.3 or higher and MySQL 4.0 or higher.
Put the RUView files in a directory inside the HTML root, and call the index.php
file from your browser. On the first run, the setup script will run automatically
and ask for your mysql credentials to create the necessary tables.

You should then log into the admin area (the default username is "admin" and the
password is "admin") ... From there, change the admin password, and (optionally) set
up user groups and permissions.

Working with RUView
-------------------
* Define your resources (team, equipment, ... etc). 
	[Screenshot](http://bigprof.com/appgini/sites/default/files/RUView-list-of-resources.png)
* Define your projects. 
	[Screenshot](http://bigprof.com/appgini/sites/default/files/RUView-managing-projects.png)
* Assign resources to projects. 
	[Screenshot](http://bigprof.com/appgini/sites/default/files/RUView-assigning-resources-to-projects.png)
* Open the chart to visualize how resources are utilized accross projects.
	[Screenshot](http://bigprof.com/appgini/sites/default/files/RUView-resources-utilization-chart.png)

About RUView
------------
RUView is based on code generated using [AppGini](http://bigprof.com/appgini/),
a PHP application generator for MySQL databases.