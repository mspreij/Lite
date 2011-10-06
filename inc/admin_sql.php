<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>Adminstration SQL syntax</title>
	<style type="text/css" media="screen">
		body {
			font-family: monospace;
			white-space: pre;
		}
		a {
			color: #0081FF;
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
	</style>
</head>

<body onload="window.focus();"># I hardly ever use these so.. some reminders.
# For many more options, <a href="http://dev.mysql.com/doc/refman/5.0/en/grant.html" title="MySQL ::   MySQL 5.0 Reference Manual :: 12.4.1.3 GRANT Syntax" target="_blank">http://dev.mysql.com/doc/refman/5.0/en/grant.html</a> (s/5.0/yourVersion/)

# == Users ==
# Creating
<span class='sample'>CREATE USER <span class='stringColor'>'joe'</span>@<span class='stringColor'>'localhost'</span> IDENTIFIED BY <span class='stringColor'>'supersekrit'</span></span> -- (yes, that's the password)

# Permissions. Basically:
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

# More fun: <a href="http://dev.mysql.com/doc/refman/5.1/en/show.html" title="MySQL ::   MySQL 5.1 Reference Manual :: 12.4.5 SHOW Syntax" target='_blank'>http://dev.mysql.com/doc/refman/5.1/en/show.html</a>