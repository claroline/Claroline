import React from 'react'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure} from './../../../utils/test'
import {ItemForm} from './item-form.jsx'

describe('<ItemForm/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(ItemForm, 'ItemForm')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<ItemForm item={{_errors: {}}}/>)
    ensure.missingProps('ItemForm', [
      'item.id',
      'validating',
      'children',
      'onChange',
      'onHintsChange'
    ])
  })

  it('has typed props', () => {
    shallow(
      <ItemForm
        item={{id: 123, _errors: {}}}
        validating={[]}
        onChange="foo"
        onHintsChange="bar"
      >
        {false}
      </ItemForm>
    )
    ensure.invalidProps('ItemForm', [
      'item.id',
      'validating',
      'children',
      'onChange',
      'onHintsChange'
    ])
  })

  it('renders a form and dispatches changes', () => {
    let updatedPath = null
    let updatedValue = null

    const form = mount(
      <ItemForm
        item={{
          id: 'ID',
          title: 'TITLE',
          description: 'DESC',
          instruction: 'INSTR',
          info: 'INFO',
          content: 'CONTENT',
          hints: [],
          feedback: 'FEEDBACK'
        }}
        validating={false}
        onChange={(path, value) => {
          updatedPath = path
          updatedValue = value
        }}
        onHintsChange={() => {}}
      >
        <input value="CHILD"/>
      </ItemForm>
    )

    ensure.propTypesOk()
    ensure.equal(form.find('form').length, 1, 'has form')

    const title = form.find('input#item-ID-title')
    ensure.equal(title.length, 1, 'has title input')
    title.simulate('change', {target: {value: 'FOO'}})
    ensure.equal(updatedPath, 'title')
    ensure.equal(updatedValue, 'FOO')
  })
})
