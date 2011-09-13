Entities within this directory are intended to offer ORM mapping for the Symfony
ACL classes, in order to integrate these classes to the schema managed by Doctrine.

This feature is needed for the use of automatic SQL generation of Doctrine migrations, 
as each table/field which is not properly mapped won't be created in the up() method 
and will be dropped in the down() method.

All the ACL entities are deactivated ('Entity' annotation removed) until a new 
installation script has been written. 