import React from 'react'

// todo : find a better place to initialize enzyme config
import Enzyme from 'enzyme'
import Adapter from 'enzyme-adapter-react-15'
Enzyme.configure({
  adapter: new Adapter()
})

import {shallow, mount} from 'enzyme'

import {propTypesOk, missingProps, invalidProps} from '#/main/core/scaffolding/tests/assert'
import {watchConsole, restoreConsole} from '#/main/core/scaffolding/tests/console'
import {mock as mockGlobals} from '#/main/core/scaffolding/tests/mock'

// assign a random name to a component, ensuring it isn't cached and thus prop
// types are always checked
// (see https://github.com/facebook/react/issues/7047#issuecomment-228614964)
function renew(component, name) {
  component.displayName = `${name}-${Math.random().toString()}`
}

/**
 * Tests common parts of all components.
 *
 *   - Checks component required props.
 *   - Checks component typed props.
 *   - Checks component rendering.
 *
 * @param {string}   name              - The name of the component.
 * @param {mixed}    Component         - The component to test.
 * @param {Array}    requiredProps     - The list of required props of the component.
 * @param {object}   invalidTypedProps - A set of invalid props (to check propTypes).
 * @param {object}   validProps        - A set of valid props (to check rendering).
 * @param {function} customDescribe    - A custom describer for component specific logic
 */
function describeComponent(name, Component, requiredProps = [], invalidTypedProps = {}, validProps = {}, customDescribe = () => {}) {
  describe(`<${name} />`, () => {
    before(mockGlobals)

    beforeEach(() => {
      watchConsole()
    })

    afterEach(restoreConsole)

    it('has required props', () => {
      shallowComponent(Component, name)

      missingProps(name, requiredProps)
    })

    it('has typed props', () => {
      shallowComponent(Component, name, invalidTypedProps)

      invalidProps(name, Object.keys(invalidTypedProps))
    })

    it('renders without error', () => {
      mountComponent(Component, name, validProps)

      propTypesOk()

      // todo checks it renders without errors
    })

    // execute custom describer
    customDescribe()
  })
}

function mountComponent(Component, name, props = {}, children = undefined) {
  // generates new component name
  renew(Component, name)

  return mount(
    React.createElement(Component, props, children)
  )
}

function shallowComponent(Component, name, props = {}, children = undefined) {
  // generates new component name
  renew(Component, name)

  return shallow(
    React.createElement(Component, props, children)
  )
}

export {
  renew, // for retro compatibility purpose
  describeComponent,
  mountComponent,
  shallowComponent
}
