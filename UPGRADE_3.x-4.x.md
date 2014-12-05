Upgrade from 3.x to 4.x
=======================

Js translator
---------------

The bazinga translator bundle has been updated. It now mimics the symfony2 translator.

See the [upgrade file][1] for the related documentation.

Symfony2 FrameworkBundle
----------------------------------

Some parameter type changed after the upgrade if you wrote your own Converters.

	use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

	Before: apply(Request $request, ConfigurationInterface $configuration)
	After: apply(Request $request, ParamConverter $configuration)

	Before: supports(ConfigurationInterface $configuration)
	After: supports(ParamConverter $configuration)

[1]: https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/UPGRADE.md

Angular
---------

Angular is now shipped with the CoreBundle (so everyone can use the same version).
"innova/angular-js-bundle": "1.0.2" is now a dependency of the CoreBundle.
