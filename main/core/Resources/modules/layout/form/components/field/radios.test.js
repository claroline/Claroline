import React from 'react'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure, mockGlobals} from '#/main/core/scaffolding/tests'
import {Radios} from './radios.jsx'

describe('<Radios/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(Radios, 'Radios')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(
      React.createElement(Radios, {
        options: []
      })
    )
    ensure.missingProps('Radios', [
      'id',
      'onChange'
    ])
  })

  it('has typed props', () => {
    shallow(
      React.createElement(Radios, {
        value: {},
        options: [{
          value: [],
          label: {}
        }],
        onChange: 'foo'
      })
    )
    ensure.invalidProps('Radios', [
      'options[0].value',
      'value',
      'onChange'
    ])
  })

  it('renders a group of radios', () => {
    let updatedValue = null

    const group = mount(
      React.createElement(Radios, {
        id: 'NAME',
        options: [
          {
            value: 'foo',
            label: 'FOO'
          }, {
            value: 'bar',
            label: 'BAR'
          }
        ],
        value: 'foo',
        onChange: value => updatedValue = value
      })
    )

    ensure.propTypesOk()

    // renders inline radios by default
    const containers = group.find('div.radio-inline')
    ensure.equal(containers.length, 2, 'has 2 inline radios containers')

    const inputs = group.find('input[type="radio"]')
    ensure.equal(inputs.length, 2, 'has 2 radios')
    ensure.equal(inputs.at(0).prop('checked'), true)
    ensure.equal(inputs.at(1).prop('checked'), false)
    inputs.at(1).simulate('change', {target: {checked: true}})
    ensure.equal(updatedValue, 'bar')
  })

  it('renders a vertical group of radios when asked', () => {
    const group = mount(
      React.createElement(Radios, {
        id: 'NAME',
        options: [
          {
            value: 'foo',
            label: 'FOO'
          }, {
            value: 'bar',
            label: 'BAR'
          }
        ],
        value: 'foo',
        onChange: () => true,
        inline: false
      })
    )

    ensure.propTypesOk()
    const containers = group.find('div.radio')
    ensure.equal(containers.length, 2, 'has 2 vertical radios containers')
  })
})
