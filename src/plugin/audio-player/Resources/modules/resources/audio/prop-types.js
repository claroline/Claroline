import {PropTypes as T} from 'prop-types'

import {User as UserType} from '#/main/community/prop-types'

const Comment = {
  propTypes: {
    id: T.string,
    content: T.string,
    meta: T.shape({
      creationDate: T.string,
      editionDate: T.string,
      user: T.shape(UserType.propTypes)
    })
  }
}

const Section = {
  propTypes: {
    id: T.string.isRequired,
    regionId: T.string,
    title: T.string,
    start: T.number.isRequired,
    end: T.number.isRequired,
    color: T.string,
    showTranscript: T.bool,
    transcript: T.string,
    commentsAllowed: T.bool,
    showHelp: T.bool,
    help: T.string,
    showAudio: T.bool,
    audioUrl: T.string,
    audioDescription: T.string,
    comment: T.shape(Comment.propTypes)
  },
  defaultProps: {
    showTranscript: false,
    commentsAllowed: false,
    showHelp: false
  }
}

const Audio = {
  propTypes: {
    hashName: T.string.isRequired,
    size: T.number.isRequired,
    url: T.string.isRequired,
    autoDownload: T.bool,
    sectionsType: T.string.isRequired,
    rateControl: T.bool.isRequired,
    description: T.string,
    sections: T.arrayOf(T.shape(Section.propTypes))
  },
  defaultProps: {
    size: 0,
    autoDownload: false
  }
}

export {
  Audio,
  Section,
  Comment
}
