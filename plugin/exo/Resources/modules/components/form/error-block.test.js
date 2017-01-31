import React from 'react'
import {shallow} from 'enzyme'
import {spyConsole, renew, ensure} from './../../utils/test'
import {ErrorBlock} from './error-block.jsx'

describe('<ErrorBlock/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(ErrorBlock, 'ErrorBlock')
  })
  afterEach(spyConsole.restore)

  it('renders an error text by default', () => {
    const block = shallow(<ErrorBlock text="ERROR"/>)
    ensure.propTypesOk()
    ensure.equal(block.hasClass('error-text'), true)
    ensure.equal(block.text(), 'ERROR')
  })

  it('renders a simple warning if needed', () => {
    const block = shallow(<ErrorBlock text="ERROR" warnOnly/>)
    ensure.propTypesOk()
    ensure.equal(block.hasClass('warning-text'), true)
  })
})
