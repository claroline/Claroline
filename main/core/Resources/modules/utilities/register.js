/**
 * Created by panos on 4/5/16.
 */
import angular from 'angular/index'

let _app = new WeakMap()
export default class Register {
  constructor (appName, deps) {
    deps = deps || []
    let module
    try {
      module = angular.module(appName)
    } catch (error) {
      module = angular.module(appName, deps)
    }
    _app.set(this, module)
  }

  config (constructorFn) {
    constructorFn = this._normalizeConstructor(constructorFn)
    let factoryArray = this._createFactoryArray(constructorFn)

    _app.get(this).config(factoryArray)

    return this
  }

  constant (name, obj) {
    _app.get(this).constant(name, obj)

    return this
  }

  controller (name, constructorFn) {
    _app.get(this).controller(name, constructorFn)

    return this
  }

  directive (name, constructorFn) {
    constructorFn = this._normalizeConstructor(constructorFn)

    if (!constructorFn.prototype.compile) {
      // create an empty compile function if none was defined
      constructorFn.prototype.compile = () => {}
    }
    let originalCompileFn = this._cloneFunction(constructorFn.prototype.compile)

    // Decorate the compile method to automatically return the link method (if it exists)
    // and bind it to the context of the constructor (so `this` works correctly).
    // This gets around the problem of a non-lexical "this" which occurs when the directive class itself
    // returns `this.link` from within the compile function.
    this._override(constructorFn.prototype, 'compile', () => {
      return function() {
        originalCompileFn.apply(this, arguments)

        if (constructorFn.prototype.link) {
          return constructorFn.prototype.link.bind(this)
        }
      }
    })

    let factoryArray = this._createFactoryArray(constructorFn)

    _app.get(this).directive(name, factoryArray)

    return this
  }

  factory (name, constructorFn) {
    constructorFn = this._normalizeConstructor(constructorFn)
    let factoryArray = this._createFactoryArray(constructorFn)
    _app.get(this).factory(name, factoryArray)

    return this
  }

  filter (name, constructorFn) {
    constructorFn = this._normalizeConstructor(constructorFn)
    let factoryArray = this._createFactoryArray(constructorFn)
    _app.get(this).filter(name, factoryArray)

    return this
  }

  provider (name, constructorFn) {
    _app.get(this).provider(name, constructorFn)

    return this
  }

  run (constructorFn) {
    constructorFn = this._normalizeConstructor(constructorFn)
    let factoryArray = this._createFactoryArray(constructorFn)

    _app.get(this).run(factoryArray)

    return this
  }

  service (name, constructorFn) {
    _app.get(this).service(name, constructorFn)

    return this
  }

  value (name, object) {
    _app.get(this).value(name, object)

    return this
  }

  /**
   * If the constructorFn is an array of type ['dep1', 'dep2', ..., constructor() {}]
   * we need to pull out the array of dependencies and add it as an $inject property of the
   * actual constructor function.
   * @param input
   * @returns {*}
   * @private
   */
  _normalizeConstructor (input) {
    let constructorFn
    if (input.constructor == Array) {
      let injected = input.slice(0, input.length - 1)
      constructorFn = input[input.length - 1]
      constructorFn.$inject = injected
    } else {
      constructorFn = input
    }

    return constructorFn
  }

  /**
   * Convert a constructor function into a factory function which returns a new instance of that
   * constructor, with the correct dependencies automatically injected as arguments.
   *
   * In order to inject the dependencies, they must be attached to the constructor function with the
   * `$inject` property annotation.
   *
   * @param constructorFn
   * @returns {Array.<T>}
   * @private
   */
  _createFactoryArray(constructorFn) {
    // get the array of dependencies that are needed by this component (as contained in the `$inject` array)
    let args = constructorFn.$inject || []
    let factoryArray = args.slice()
    // The factoryArray uses Angular's array notation whereby each element of the array is the name of a
    // dependency, and the final item is the factory function itself.
    factoryArray.push((...args) => {
      // return new constructorFn(...args)
      let instance = new constructorFn(...args)
      for (let key in instance) {
        instance[key] = instance[key]
      }
      return instance
    })

    return factoryArray
  }

  /**
   * Clone a function
   * @param original
   * @returns {Function}
   * @private
   */
  _cloneFunction (original) {
    return function() {
      return original.apply(this, arguments);
    }
  }

  /**
   * Override an object's method with a new one specified by `callback`
   * @param object
   * @param method
   * @param callback
   * @private
   */
  _override (object, method, callback) {
    object[method] = callback(object[method])
  }
}