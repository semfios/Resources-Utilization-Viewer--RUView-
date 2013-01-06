<?php header('Content-type: text/css'); ?>
#children-menu{
	width: auto;
	background: #fff;
	border-bottom: 1px solid #000;
	overflow: hidden;
	position: relative;
}
#children-menu ul{
	clear: left;
	float: left;
	list-style: none;
	margin: 0;
	padding: 0;
	position: relative;
	text-align: center;
}
#children-menu ul li{
	display: block;
	float: left;
	list-style: none;
	margin: 0 1px;
	padding: 0;
	position: relative;
	border-radius: 5px 5px 0 0;
}
#children-menu ul li a{
	display: block;
	margin: 0 0 0 30px;
	padding: 3px 10px;
	background: transparent;
	text-decoration: none;
	line-height: 1.9em;
}
#children-menu ul li a:focus{ outline: 0; }
#children-menu ul li a:hover{
}
#children-menu ul li a.active,
#children-menu ul li a.active:hover{
	line-height: 2.4em;
}
#children-menu ul li.TableBody{ margin-top: 0.5em !important; }

.panel>*{ margin: 0; }
.panel{
	background: none repeat scroll 0 center;
	display: block;
	padding: 10px;
	border: dotted 1px silver;
	border-top: none;
}

.panel td{ padding: 1px 4px; text-indent: 0; }
.panel td:hover{ color: inherit; }
.panel td.TableHeader{ padding: 4px; }
.panel td.toolbar div{ border: solid 1px transparent; float: left; margin: 0 5px 0 0; cursor: pointer; padding: 2px; }
.panel td.toolbar div:hover{ border: solid 1px Gold; background: Gold; border-radius: 4px; }
.panel td.view-on-click:hover{ background: Gold !important; cursor: pointer; }
iframe{ border: none; overflow: auto; }

.TextBox{ margin: 0 4px 2px 0; padding: 1px; }

#pc-loading{ background: none repeat scroll 0 0 yellow; font-family: arial; left: 3px; margin-top: -10px; opacity: 0.85; position: absolute; top: 20px; width: 150px; }
