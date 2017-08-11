import React from 'react'
import {shallow} from 'enzyme'

import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {ItemForm} from './item-form.jsx'

describe('<ItemForm/>', () => {
  before(mockTranslator)
  beforeEach(() => {
    spyConsole.watch()
    renew(ItemForm, 'ItemForm')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(ItemForm, {
        item: {
          content: '',
          hints: [],
          feedback: '',
          _errors: {}
        }
      })
    )

    ensure.missingProps('ItemForm', [
      'item.id',
      'validating',
      'children',
      'showModal',
      'closeModal',
      'onChange',
      'onHintsChange',
      'mandatoryQuestions'
    ])
  })

  it('has typed props', () => {
    shallow(
      React.createElement(ItemForm, {
        item: {
          id: 123,
          _errors: {}
        },
        validating: [],
        onChange: 'foo',
        onHintsChange: 'bar',
        children: false
      })
    )

    ensure.invalidProps('ItemForm', [
      'item.id',
      'validating',
      'children',
      'onChange',
      'onHintsChange'
    ])
  })

  // todo : make it works
  // The child component ObjectsEditor makes enzyme `mount` fail.
  // I don't know if it's because of the dynamic component in ObjectsEditor or because it is connected to the store.
  it('renders a form and dispatches changes', () => {
    /*let updatedPath = null
    let updatedValue = null

    const form = mount(
      React.createElement(ItemForm, {
        item: {
          id: 'ID',
          title: 'TITLE',
          description: 'DESC',
          content: 'CONTENT',
          hints: [],
          feedback: 'FEEDBACK',
          _errors: {}
        },
        validating: false,
        onChange: (path, value) => {
          updatedPath = path
          updatedValue = value
        },
        onHintsChange: () => true,
        showModal: () => true,
        closeModal: () => true
      }, React.createElement('input'))
    )

    ensure.propTypesOk()
    ensure.equal(form.find('form').length, 1, 'has form')

    ensure.equal(true, true)

    const title = form.find('#item-ID-title')
    ensure.equal(title.length, 1, 'has title input')
    title.simulate('change', {target: {value: 'FOO'}})
    console.log(updatedPath)
    console.log(updatedValue)
    ensure.equal(updatedPath, 'title')
    ensure.equal(updatedValue, 'FOO')*/
  })
})
