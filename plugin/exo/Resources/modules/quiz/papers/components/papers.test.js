import React from 'react'
import {mount} from 'enzyme'
import configureMockStore from 'redux-mock-store'
import {spyConsole, renew, ensure, mockTranslator} from './../../../utils/test'
import {Papers} from './papers.jsx'

describe('<Papers/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(Papers, 'Papers')
    mockTranslator()
  })
  afterEach(spyConsole.restore)

  it('renders a list of papers', () => {
    const store = configureMockStore()({
      quiz: {
        meta: {
          editable: true
        }
      },
      papers: {
        papers: [
          {
            id: '123',
            number: 1,
            user: {
              name: 'John Doe'
            },
            startDate: '1986/02/12',
            finished: true
          },
          {
            id: '456',
            number: 2,
            user: {
              name: 'Jane Doe'
            },
            startDate: '2015/11/03',
            finished: false
          }
        ]
      }
    })

    const papers = mount(<Papers store={store}/>)

    ensure.propTypesOk()
    ensure.equal(papers.find('table').length, 1)
    ensure.equal(papers.find('tr').length, 3) // 2 papers + 1 header line
  })
})
