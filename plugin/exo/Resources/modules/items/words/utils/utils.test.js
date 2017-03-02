import {utils} from './utils'
import {ensure} from './../../../utils/test'

describe('Test splitting function', () => {
  it('Test the splitting function', () => {
    const text = 'Clarobot is very NICE and carefree but sometimes a little bit boring'
    const answers = [
      {
        'text': 'nice',
        'score': 1,
        'feedback': 'yes he is !',
        'caseSensitive': true
      },
      {
        'text': 'carefree',
        'score': 1,
        'feedback': 'yes he is !',
        'caseSensitive': false
      },
      {
        'text': 'boring',
        'score': -1,
        'feedback': 'SHAME ON YOU !',
        'caseSensitive': false
      }
    ]

    const splitted = utils.split(text, answers, false)
    ensure.equal(splitted[0].text, 'Clarobot is very NICE and carefree')
    ensure.equal(splitted[1].text, ' but sometimes a little bit boring')
  })

})
