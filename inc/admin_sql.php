<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js" type="text/javascript" charset="utf-8"></script>
	<title>Adminstration SQL syntax pointers</title>
	<style type="text/css" media="screen">
		body {
			font-family: "ubuntu mono", monospace;
			font-size: 12pt;
		}
		a {
			color: #0081FF;
			cursor: pointer;
		}
		a.local {
			color: green;
		}
		a.tab {
			color: green;
			text-decoration: none;
			border: 1px outset #8C8;
			padding: 0px 10px;
			margin-right: 10px;
		}
		a.active {
			cursor: default;
		}
		a.tab.active {
			color: black;
			border: 1px inset #8C8;
			background: #A5D77C;
			font-weight: bold;
		}
		a.tab.active:hover {
		}
		.topref {
			font-style: italic;
		}
		.target {
			white-space: pre;
		}
		.sample {
			color: #800;
			background: #DFDFDF;
			padding: 0px 10px;
		}
		.sampleColor {
			color: #800;
		}
		.stringColor {
			color: #008;
		}
		.target > strong:first-child {
			color: green;
		}
	</style>
	<script type="text/javascript">
	$(document).ready(function(){
		$('.target').hide().filter(':first').show();
		$('.topref').remove();
		
		$('.tab, #disclaimerLink').click(function(e) {
			var _this = $(this);
			if (_this.hasClass('active')) return false;
			$('.tab, #disclaimerLink').removeClass('active');
			$('.target').slideUp();
			$('#'+$(this).data('target')).slideDown();
			$(this).addClass('active');
			e.preventDefault();
		});
	});
	</script>
</head>

<body onload="window.focus();" id='top'>
<p># I hardly ever use these so.. here are some reminders &amp; pointers I wrote for myself, while using Lite, say.<br>
# I hope to [remember to] update it as I learn more good things and/or unlearn more bad things. <a href='#disclaimer' class='local' style='font-style: italic;' data-target='disclaimer' id='disclaimerLink'>(disclaimer)</a></p>

<a class='tab active' data-target='users' href='#users'>Account Administration</a>
<a class='tab' data-target='characterSets' href='#characterSets'>Character Sets</a>
<a class='tab' data-target='moreFun' href='#moreFun'>More Fun</a>

<!-- Users -->
<div class='target' id='users'>
# <strong>== Account Administration ==</strong> <a href='#top' class='local topref'>top&uparrow;</a>
# Creating
<span class='sample'>CREATE USER <span class='stringColor'>'joe'</span>@<span class='stringColor'>'localhost'</span> IDENTIFIED BY <span class='stringColor'>'supersekrit'</span></span> -- (yes, that's the password)

# <strong>Permissions.</strong> Basically:
#   <span class='sampleColor'>GRANT</span> {permissions, ..} <span class='sampleColor'>ON</span> {target} <span class='sampleColor'>TO</span> {user[@host]}
# 
# Subset of permissions:
#   <span class='sampleColor'>ALL</span>                              -- every permission applicable to {target} (except for GRANT)
#   <span class='sampleColor'>SELECT, INSERT, UPDATE, DELETE</span>   -- a.k.a. CRUD
#   <span class='sampleColor'>CREATE, ALTER, INDEX, DROP</span>       -- CRUD for tables
#   <span class='sampleColor'>USAGE</span>                            -- none (and implied by CREATE USER), but useful in other statements
# 
# Targets: db_name.*           -- specified database
#          db_name.table_name  -- specified table
#                *.*           -- Global (all databases and then some, for other types of permission)
# 
# GRANT statements are accumulative and cannot be used to REVOKE permissions.
# Use <span class='sampleColor'>SHOW GRANTS [FOR <span class='stringColor'>'username'</span>]</span> to see the current permissions.
# 
# If the user doesn't already exist, add: IDENTIFIED BY 'password' (but, just create before you grant, hmkay?)
<span class='sample'>GRANT SELECT, INSERT, UPDATE, DELETE ON <span class='stringColor'>theCornerShop.*</span> TO <span class='stringColor'>'joe'</span>@<span class='stringColor'>'localhost'</span></span>

# The reverse is <span class='sampleColor'>REVOKE</span> {permissions, ..} <span class='sampleColor'>ON</span> {target} <span class='sampleColor'><strong>FROM</strong></span> {user[@host]}
# This is Useful for MySQL &lt; 5.0.2, which can only <span class='sampleColor'>DROP USER <span class='stringColor'>'username'</span></span> for accounts that have No Privileges.
# See the sordid details at <a href="http://dev.mysql.com/doc/refman/5.0/en/drop-user.html" title="MySQL ::   MySQL 5.0 Reference Manual :: 12.4.1.2 DROP USER Syntax" target='_blank'>http://dev.mysql.com/doc/refman/5.0/en/drop-user.html</a>
# From 5.0.2 onwards you can just
<span class='sample'>DROP USER <span class='stringColor'>'joe'</span></span>

# to remove the account record *and* all related privileges records.

# Recap and then some:
<span class='sample'>CREATE USER <span class='stringColor'>'joe'</span>@<span class='stringColor'>'localhost'</span> IDENTIFIED BY <span class='stringColor'>'supersekrit'</span></span>                     -- create user account
<span class='sample'>GRANT SELECT, INSERT, UPDATE, DELETE ON <span class='stringColor'>theCornerShop.*</span> TO <span class='stringColor'>'joe'</span>@<span class='stringColor'>'localhost'</span></span>  -- grant permissions example
<span class='sample'>SHOW GRANTS [FOR <span class='stringColor'>'username'</span>]</span>                                                  -- shows your or specified user's permissions
<span class='sample'>REVOKE DELETE, UPDATE ON <span class='stringColor'>'theCornerShop'.'localhost'</span> FROM <span class='stringColor'>'jim'@'localhost'</span></span>   -- remove permissions
<span class='sample'>DROP USER 'leetHax0r1994'</span>                                                     -- delete user

# For many more options, <a href="http://dev.mysql.com/doc/refman/5.0/en/grant.html" title="MySQL ::   MySQL 5.0 Reference Manual :: 12.4.1.3 GRANT Syntax" target="_blank">http://dev.mysql.com/doc/refman/5.0/en/grant.html</a> (s/5.0/yourVersion/)
</div>

<!-- Character Sets -->
<div class='target active' id='characterSets'>
# <strong>== Character Sets ==</strong> <a href='#top' class='local topref'>top&uparrow;</a>
# Messing with character sets: <span title="More than climbing cacti while on fire!">So Much Fun!</span>
# These pointers assume UTF-8, if you're using something else: sorry. (Also, the disclaimer applies <em>twice</em> to this section.)

# Now, when the HTTP headers are utf8, and the data is utf8, and you have a utf8 metatag characterset (which is overruled
# by the HTTP header anyway..), make sure the database, tables and fields are also utf8. Then, make sure the connection
# is utf8, and the client character set is utf8. Also, the result character set should be utf8. Those last 3 you can do
# in one go with:
<span class='sample'>SET NAMES utf8</span>
# Do this as first query (like, right after mysql_connect()).

# To check what, currently, is and is not utf8, these should give you <em>some</em> idea:
<span class='sample'>SHOW variables WHERE variable_name LIKE <span class='stringColor'>'%coll%'</span> OR variable_name LIKE <span class='stringColor'>'%char%'</span></span>
<span class='sample'>SHOW CREATE TABLE <span class='stringColor'>`tableName`</span></span>

# With the latter pay attention to <span class='sampleColor'>DEFAULT CHARSET=utf8</span> in the last line, and any mention of character related
# things after the column definition lines.
# Also note: <em>even</em> if you can see the proper Arabic glyphs (or whatever you stored) in the command-line client
# via a proper modern terminal emulator, <em>that does not mean</em> the table or field character sets are utf8.

# To change the character set of a single column, use
<span class="sample">ALTER TABLE <span class='stringColor'>`tableName`</span> MODIFY <span class='stringColor'>`colName`</span> varchar(12) CHARACTER SET <span class='stringColor'>utf8</span></span>
# To change the default character set of a table:
<span class='sample'>ALTER TABLE <span class='stringColor'>`tableName`</span> CHARACTER SET <span class='stringColor'>utf8</span></span>
# To change the default character set of a table <strong><em>and</em></strong> all its columns:
<span class='sample'>ALTER TABLE <span class='stringColor'>`tableName`</span> CONVERT TO CHARACTER SET <span class='stringColor'>utf8</span></span>
</div>

<!-- More Fun -->
<div class='target' id='moreFun'>
# <strong>== More Fun ==</strong> <a href='#top' class='local topref'>top&uparrow;</a>
<span class='sample'>SHOW variables</span> -- server information
<span class='sample'>SHOW status</span>    -- <em>more</em> server information!

# You can 'filter' the results like <span class='sampleColor'>SHOW variables WHERE variable_name LIKE <span class='stringColor'>'%substr%'</span></span>, or on value of course.
# And welll... <a href="http://dev.mysql.com/doc/refman/5.1/en/show.html" title="MySQL ::   MySQL 5.1 Reference Manual :: 12.4.5 SHOW Syntax" target='_blank'>http://dev.mysql.com/doc/refman/5.1/en/show.html</a>
</div>

<!-- disclaimer -->
<div class='target' id='disclaimer'>
<strong>== Disclaimer ==</strong> <a href='#top' class='local topref'>top&uparrow;</a>
This text was put together by somebody who does do web development for a living, but has no DBA certifications or papers
<em>of any kind</em>. Also (sadly), he is only human, and therefore imperfect.
If you find any errors/inconsistencies/typors, please let him know at gmail.com@mspreij.
</div>

</body>
</html>
<?php
/* -- Log --------------------------------

[2011-10-09 01:14:54] added 'tab' links (yeah I don't know either man)

Todo: make the tab thingers listen to the query string so you can point at admin_sql.php?active=characterSet or somesuch.
      (since when did this file started needing a "todo"?!)

*/
?>