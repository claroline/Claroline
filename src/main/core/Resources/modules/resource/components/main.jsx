import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import {Helmet} from 'react-helmet'

import {theme} from '#/main/theme/config'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {makeCancelable} from '#/main/app/api'
import {route as toolRoute} from '#/main/core/tool/routing'
import {getResource} from '#/main/core/resources'
import {withReducer} from '#/main/app/store/reducer'

import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentNotFound} from '#/main/app/content/components/not-found'

const Resource = props => {
  if (props.loaded) {
    return (
      <>
        {props.children}

        {0 !== props.styles.length &&
          <Helmet>
            {props.styles.map(style =>
              <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
            )}
          </Helmet>
        }
      </>
    )
  }

  return (
    <ContentLoader
      size="lg"
      description={trans('loading', {}, 'resource')}
    />
  )
}

Resource.propTypes = {
  loaded: T.bool.isRequired,
  styles: T.arrayOf(T.string),
  children: T.node
}

Resource.defaultProps = {
  styles: []
}

class ResourceMain extends Component {
  constructor(props) {
    super(props)

    this.state = {
      type: null,
      component: null,
      store: null,
      styles: []
    }

    this.loadApp = this.loadApp.bind(this)
  }

  componentDidMount() {
    if (!this.props.loaded) {
      this.props.open(this.props.resourceSlug, this.props.embedded, this.loadApp)
    } else {
      this.loadApp(this.props.resourceType)
    }
  }

  componentDidUpdate(prevProps) {
    if (!this.props.loaded && this.props.loaded !== prevProps.loaded) {
      this.props.open(this.props.resourceSlug, this.props.embedded, this.loadApp)
    }
  }

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
      this.pending = null
    }
  }

  loadApp(resourceType) {
    if (!this.pending) {
      this.pending = makeCancelable(getResource(resourceType))

      this.pending.promise
        .then(
          (resolved) => {
            if (resolved.default) {
              this.setState({
                type: resourceType,
                app: resolved.default.store ? withReducer(resourceType, resolved.default.store)(Resource) : Resource,
                component: resolved.default.component,
                styles: resolved.default.styles || []
              })
            }
          },
          (errors) => {
            // TODO : find better.
            /* eslint-disable no-console */
            console.error(errors)
            /* eslint-enable no-console */
          }
        )
        .then(
          () => this.pending = null,
          () => this.pending = null
        )
    }

    return this.pending.promise
  }

  render() {
    if (!this.state.type) {
      return (
        <ContentLoader
          size="lg"
          description={trans('loading', {}, 'resource')}
        />
      )
    }

    if (this.props.notFound) {
      return (
        <ContentNotFound
          size="lg"
          title={trans('not_found', {}, 'resource')}
          description={trans('not_found_desc', {}, 'resource')}
        >
          {!this.props.embedded &&
            <Button
              variant="btn"
              size="lg"
              type={LINK_BUTTON}
              label={trans('browse-resources', {}, 'actions')}
              target={toolRoute('resources')}
              exact={true}
              primary={true}
            />
          }
        </ContentNotFound>
      )
    }

    if (this.state.app) {
      return createElement(this.state.app, {
        path: this.props.path,
        loaded: this.props.loaded,
        styles: this.state.styles,
        children: this.state.component && createElement(this.state.component, {
          path: this.props.path
        })
      })
    }

    return null
  }
}

ResourceMain.propTypes = {
  path: T.string.isRequired,
  resourceSlug: T.string.isRequired,
  resourceType: T.string,

  embedded: T.bool.isRequired,
  loaded: T.bool.isRequired,
  notFound: T.bool.isRequired,
  open: T.func.isRequired
}

export {
  ResourceMain
}
