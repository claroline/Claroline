import {
  ensure,
  describeComponent,
  mountComponent
} from '#/main/core/scaffolding/tests'

import {ToggleableSet} from './toggleable-set.jsx'

describeComponent('ToggleableSet', ToggleableSet,
  // required props
  [
    'showText',
    'hideText',
    'children'
  ],
  // invalid props
  {
    showText: 123,
    hideText: false,
    children: {toto: 'TOTO'}
  },
  // valid props
  {
    showText: 'Show section',
    hideText: 'Hide section',
    children: 'Bar'
  },
  // custom tests
  () => {
    it('Renders a link to toggle section', () => {
      const section = mountComponent(ToggleableSet, {
        showText: 'Show section',
        hideText: 'Hide section',
        children: 'Bar'
      })

      // check propTypes
      ensure.propTypesOk()

      // show
      const showLink = section.find('.toggleable-set-toggle').at(0)
      ensure.equal(showLink.name(), 'a')
      ensure.equal(showLink.text(), 'Show section')

      showLink.simulate('click')

      // hide
      const hideLink = section.find('.toggleable-set-toggle').at(0)
      ensure.equal(hideLink.name(), 'a')
      ensure.equal(hideLink.text(), 'Hide section')
    })
  }
)
