import React from 'react'
import {mount} from 'enzyme'
import configureMockStore from 'redux-mock-store'
import {spyConsole, renew, ensure, mockTranslator} from './../../../utils/test'
import {Answers} from './answers.jsx'

describe('<Answers/>', () => {
  beforeEach(() => {
    spyConsole.watch()
    renew(Answers, 'Answers')
    mockTranslator()
  })
  afterEach(spyConsole.restore)

  it('renders a list of answers to correct', () => {
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
        ],
        currentQuestionId: 'q123'
      }
    })

    const answers = mount(<Answers store={store}/>)

    ensure.propTypesOk()
    ensure.equal(answers.find('div.answers-list').length, 1)
    ensure.equal(answers.find('div.answer-row').length, 2)
  })
})
