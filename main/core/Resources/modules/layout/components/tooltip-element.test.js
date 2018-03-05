import React from 'react'

import {
  ensure,
  describeComponent,
  mountComponent
} from '#/main/core/scaffolding/tests'

import {TooltipElement} from './tooltip-element.jsx'

describeComponent('TooltipElement', TooltipElement,
  // required props
  [
    'id',
    'tip',
    'children'
  ],
  // invalid props
  {
    id: 123,
    tip: {},
    position: 'bla',
    children: {}
  },
  // valid props
  {
    id: 'ID',
    tip: 'TIP',
    children: React.createElement('span', {}, 'CONTENT')
  },
  // custom tests
  () => {
    it('renders an element with a tooltip', () => {
      const element = mountComponent(TooltipElement, {
        id: 'ID',
        tip: 'TIP',
        children: React.createElement('span', {}, 'CONTENT')
      })

      ensure.propTypesOk()
      ensure.equal(element.text(), 'CONTENT')
      // not sure if/how the tooltip itself should be tested
    })
  }
)
