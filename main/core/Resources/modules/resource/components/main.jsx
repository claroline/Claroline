import React, {Fragment, Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {theme} from '#/main/app/config'
import {withReducer} from '#/main/app/store/components/withReducer'
import {makeCancelable} from '#/main/app/api'
import {Await} from '#/main/app/components/await'
import {getResource} from '#/main/core/resources'

import {ContentLoader} from '#/main/app/content/components/loader'

const Resource = props =>
  <Fragment>
    {props.children}

    {props.styles && props.styles.map(style =>
      <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
    )}
  </Fragment>

Resource.propTypes = {
  styles: T.arrayOf(T.string),
  children: T.node
}

class ResourceMain extends Component {
  constructor(props) {
    super(props)

    this.state = {
      loaded: false
    }
  }

  componentDidMount() {
    this.load()
  }

  componentDidUpdate(prevProps) {
    if (this.props.resourceId !== prevProps.resourceId) {
      if (this.pending) {
        this.pending.cancel()
      }

      this.load()
    }
  }

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
      this.pending = null
    }
  }

  load() {
    this.setState({loaded: false})

    this.pending = makeCancelable(this.props.loadNode(this.props.resourceId))
    this.pending.promise
      .then(() => {
        this.setState({loaded: true})

        if (this.props.open) {
          this.props.open()
        }
      })
      .then(
        () => this.pending = null,
        () => this.pending = null
      )
  }

  render() {
    if (!this.state.loaded) {
      return (
        <ContentLoader
          size="lg"
          description="Nous recherchons votre ressource"
        />
      )
    }

    return (
      <Await
        for={getResource(this.props.resourceType)}
        placeholder={
          <ContentLoader
            size="lg"
            description="Nous ouvrons votre ressource"
          />
        }
        then={module => {
          const ResourceApp = withReducer(this.props.resourceType, module.default.store)(Resource)

          return (
            <ResourceApp
              styles={module.default.styles}
            >
              {module.default.component && createElement(module.default.component, {
                path: this.props.path
              })}
            </ResourceApp>
          )
        }}
      />
    )
  }
}

ResourceMain.propTypes = {
  path: T.string.isRequired,
  resourceId: T.string.isRequired,
  resourceType: T.string,

  loaded: T.bool.isRequired,
  open: T.func,
  loadNode: T.func.isRequired
}

export {
  ResourceMain
}
