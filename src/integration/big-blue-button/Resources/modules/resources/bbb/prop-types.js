import {PropTypes as T} from 'prop-types'

const BBB = {
  propTypes: {
    id: T.string,
    welcomeMessage: T.string,
    newTab: T.bool,
    moderatorRequired: T.bool,
    record: T.bool,
    ratio: T.number,
    activated: T.bool,
    customUsernames: T.bool,
    runningOn: T.string,
    restrictions: T.shape({
      disabled: T.bool,
      server: T.string
    })
  }
}

const Recording = {
  propTypes: {
    id: T.string,
    status: T.string,
    startTime: T.string,
    endTime: T.string,
    participants: T.number,
    medias: T.shape({
      podcast: T.string,
      presentation: T.string
    }),
    // this contains info from resource node
    meeting: T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired,
      slug: T.string.isRequired
    })
  }
}

export {
  BBB,
  Recording
}