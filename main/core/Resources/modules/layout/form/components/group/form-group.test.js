import React from 'react'

import {
  ensure,
  describeComponent,
  mountComponent,
  shallowComponent
} from '#/main/core/scaffolding/tests'

import {FormGroup} from './form-group.jsx'

describeComponent('FormGroup', FormGroup,
  // required props
  [
    'id',
    'label',
    'children'
  ],
  // invalid props
  {
    id: true,
    label: 123,
    warnOnly: '456',
    children: {
      toto: true
    }
  },
  // valid props
  {
    id: 'ID',
    label: 'LABEL',
    children: React.createElement('span', {children: 'CHILD'})
  },
  // custom tests
  () => {
    it('renders a label and a given field', () => {
      const group = shallowComponent(FormGroup, 'FormGroup', {
        id: 'ID',
        label: 'LABEL',
        children: React.createElement('span', {children: 'CHILD'})
      })

      // check propTypes
      ensure.propTypesOk()

      // search form group container
      ensure.equal(group.name(), 'div')
      ensure.equal(group.hasClass('form-group'), true)
      ensure.equal(group.children().length, 2)

      // search for group label
      const label = group.childAt(0)
      ensure.equal(label.name(), 'label')
      ensure.equal(label.hasClass('control-label'), true)
      ensure.equal(label.props().htmlFor, 'ID')

      // search for group content
      const child = group.childAt(1)
      ensure.equal(child.text(), 'CHILD')
    })

    it('displays an help text if any', () => {
      const group = mountComponent(FormGroup, 'FormGroup', {
        id: 'ID',
        label: 'LABEL',
        help: 'HELP',
        children: React.createElement('span', {children: 'CHILD'})
      })

      // check propTypes
      ensure.propTypesOk()

      // search for help
      ensure.equal(group.find('.help-block').text(), 'HELP')
    })

    it('displays an error if any', () => {
      const group = mountComponent(FormGroup, 'FormGroup', {
        id: 'ID',
        label: 'LABEL',
        error: 'ERROR',
        children: React.createElement('span', {children: 'CHILD'})
      })

      // check propTypes
      ensure.propTypesOk()

      // search for error
      ensure.equal(group.find('.error-block').text(), 'ERROR')
    })
  }
)
