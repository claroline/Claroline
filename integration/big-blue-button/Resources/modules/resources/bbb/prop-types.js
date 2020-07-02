import {PropTypes as T} from 'prop-types'

const BBB = {
  propTypes: {
    id: T.string,
    welcomeMessage: T.string,
    newTab: T.boolean,
    moderatorRequired: T.boolean,
    record: T.boolean,
    ratio: T.number,
    activated: T.boolean,
    customUsernames: T.boolean,
    restrictions: T.shape({
      server: T.string,
      dates: T.arrayOf(T.string)
    })
  }
}

const Recording = {
  propTypes: {
    recordID: T.string,
    meetingID: T.string,
    name: T.string,
    state: T.string,
    startTime: T.number,
    endTime: T.number,
    participants: T.number,
    media: T.shape({
      podcast: T.string,
      presentation: T.string
    })
  }
}

export {
  BBB,
  Recording
}