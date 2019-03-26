import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Hourglass} from '#/main/app/animation/components/hourglass'
import {ProgressBar} from '#/main/core/layout/components/progress-bar'

class ContentLoader extends Component {
  constructor(props) {
    super(props)

    this.state = {
      progress: 0
    }

    this.fakeProgress = this.fakeProgress.bind(this)
  }

  componentDidMount() {
    this.progression = setInterval(this.fakeProgress, 100)
  }

  componentWillUnmount() {
    if (this.progression) {
      clearInterval(this.progression)
    }
  }

  fakeProgress() {
    const progress = Math.floor(((100 - this.state.progress) / 20) * Math.random() * 100) / 100

    this.setState({
      progress: this.state.progress + progress
    })
  }

  render() {
    return (
      <div className={classes('content-loader', {
        [`content-loader-${this.props.size}`]: !!this.props.size,
        [`content-loader-${this.props.direction}`]: !!this.props.direction
      })}>
        <div className="content-loader-animation">
          <Hourglass />
        </div>

        Merci de patienter quelques instants
      </div>
    )
  }
}

ContentLoader.propTypes = {
  size: T.oneOf(['sm', 'lg']),
  direction: T.oneOf(['horizontal', 'vertical'])
}

export {
  ContentLoader
}
