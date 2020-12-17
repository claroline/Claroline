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