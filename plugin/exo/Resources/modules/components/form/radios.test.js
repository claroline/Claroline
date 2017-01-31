import React from 'react'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure} from './../../utils/test'
import {Radios} from './radios.jsx'

describe('<Radios/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(Radios, 'Radios')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<Radios options={[]}/>)
    ensure.missingProps('Radios', [
      'groupName',
      'checkedValue',
      'onChange'
    ])
  })

  it('has typed props', () => {
    shallow(
      <Radios
        groupName={false}
        options={[{
          value: [],
          label: {}
        }]}
        checkedValue={123}
        onChange="foo"
      />
    )
    ensure.invalidProps('Radios', [
      'groupName',
      'options[0].value',
      'checkedValue',
      'onChange'
    ])
  })

  it('renders a group of radios', () => {
    let updatedValue = null

    const group = mount(
      <Radios
        groupName="NAME"
        options={[
          {
            value: 'foo',
            label: 'FOO'
          },
          {
            value: 'bar',
            label: 'BAR'
          }
        ]}
        checkedValue="foo"
        onChange={value => updatedValue = value}
      />
    )

    ensure.propTypesOk()
    const inputs = group.find('input[type="radio"]')
    ensure.equal(inputs.length, 2, 'has 2 radios')
    ensure.equal(inputs.at(0).prop('checked'), true)
    ensure.equal(inputs.at(1).prop('checked'), false)
    inputs.at(1).simulate('change', {target: {checked: true}})
    ensure.equal(updatedValue, 'bar')
  })

  it('renders an inline group of radios when asked', () => {

    const group = mount(
      <Radios
        groupName="NAME"
        options={[
          {
            value: 'foo',
            label: 'FOO'
          },
          {
            value: 'bar',
            label: 'BAR'
          }
        ]}
        checkedValue="foo"
        onChange={() => {}}
        inline={true}
      />
    )

    ensure.propTypesOk()
    const containers = group.find('div.radio-inline')
    ensure.equal(containers.length, 2, 'has 2 inline radios containers')
  })
})
