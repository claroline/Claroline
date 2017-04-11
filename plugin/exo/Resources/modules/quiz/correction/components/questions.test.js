import React from 'react'
import {mount} from 'enzyme'
import configureMockStore from 'redux-mock-store'
import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {Questions} from './questions.jsx'

describe('<Questions/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(Questions, 'Questions')
    mockTranslator()
  })
  afterEach(spyConsole.restore)

  it('renders a list of questions to correct', () => {
    const store = configureMockStore()({
      correction: {
        questions: [
          {
            id: 'q123',
            type: 'application/x.open+json',
            content: 'Content of question q123',
            title: 'Question #1',
            score: {
              type: 'manual',
              max: 20
            }
          },
          {
            id: 'q456',
            type: 'application/x.open+json',
            content: 'Content of question 4q56',
            score: {
              type: 'manual',
              max: 100
            }
          }
        ],
        answers: [
          {
            id: 'a789',
            questionId: 'q123',
            data: 'Content of answer a789'
          },
          {
            id: 'a012',
            questionId: 'q456',
            data: 'Content of answer a012'
          },
          {
            id: 'a345',
            questionId: 'q123',
            data: 'Content of answer a345'
          }
        ]
      }
    })

    const questions = mount(<Questions store={store}/>)

    ensure.propTypesOk()
    ensure.equal(questions.find('table').length, 1)
    ensure.equal(questions.find('tr').length, 3) // 2 questions + 1 header line
  })
})
