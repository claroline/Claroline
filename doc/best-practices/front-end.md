[[Documentation index]][1]

Best Practices : Javascript
=================

Code structure
---------------
* Javascript code MUST be stored in `MyBundle/Resources/modules`.
* Javascript code MUST be served through `webpack`.
* For complex application, the code SOULD be separated into small functional AngularJS modules and aggregated into a master module


JavaScript consideration
---------------
* Code SHOULD be written in ES6.


AngularJS consideration
---------------
* You SHOULD NOT use the Angular's `$scope` to pass variables and functions to the view. Use controller properties instead. The `$scope` SHOULD only be used when adding an event through `$watch()`.
* Controllers SHOULD be as short as possible. Write your logic in Services. 
* You SHOULD NOT directly manipulate the DOM with Angular. Most of the time, there is no need to manually modify the DOM. If you need to do it, you SHOULD do it into the Directive (not into Controllers or Services)
* You MUST NOT use jQuery at all. In many case, there are directives to achieve the same task. jQuery is only needed for complexe event handlers like `droppable`, `draggable`, etc.


Third party libraries
---------------
* The set of used libraries SHOULD be as little as possible.
* Before adding a library which is not already included in the project, you MUST discuss about its pros and cons with the community.
* If you only need few functions from a third party library you SHOULD consider writting it yourself. 


External links
---------------

* [Official AngularJS v1 documentation][2]
* [Best practices AngularJS v1 written in ES5][3]
* [Best practices AngularJS v1 written in ES6][4]

[[Documentation index]][1]

[1]:  ../index.md
[2]:  https://angularjs.org/
[3]:  https://github.com/johnpapa/angular-styleguide
[4]:  https://github.com/rwwagner90/angular-styleguide-es6
