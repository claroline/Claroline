import React, {Component}  from 'react'
import {PropTypes as T} from 'prop-types'

class YouTubePlayer extends Component {
  constructor(props) {
    super(props)

    this.player = null
    this.timer = null
  }

  componentDidMount() {
    this.player = new window.YT.Player(`youtube-player`, {
      width: '560',
      height: '315',
      videoId: this.props.videoId,
      events : {
        onStateChange: (event) => {
          switch (event.data) {
            case window.YT.PlayerState.PLAYING:
              this.props.onPlay(event.target.getCurrentTime(), event.target.getDuration())
              this.timer = setInterval( this.onTimer, 1000)
              break
            case window.YT.PlayerState.PAUSED:
              this.props.onPause(event.target.getCurrentTime(), event.target.getDuration())
              clearInterval(this.timer)
              break
          }
        }
      }
    })
  }

  onTimer() {
    this.props.onTimeUpdate( this.player.getCurrentTime(), this.player.getDuration() )
  }

  render() {
    return (
      <div className="youtube-player-container">
        <div id={`youtube-player`} />
      </div>
    )
  }
}

YouTubePlayer.propTypes = {
  videoId: T.string.isRequired,
  onPlay: T.func,
  onPause: T.func,
  onTimeUpdate: T.func
}

export {
  YouTubePlayer
}
