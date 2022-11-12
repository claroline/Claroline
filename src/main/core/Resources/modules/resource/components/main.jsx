import React, {Fragment, Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import {ReactReduxContext} from 'react-redux'
import {Helmet} from 'react-helmet'

import {theme} from '#/main/theme/config'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {makeCancelable} from '#/main/app/api'
import {route as toolRoute} from '#/main/core/tool/routing'
import {getResource} from '#/main/core/resources'

import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentNotFound} from '#/main/app/content/components/not-found'

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
    if (!this.props.notFound && this.props.resourceSlug !== prevProps.resourceSlug) {
      this.props.close(prevProps.resourceSlug, prevProps.embedded)
    }

    if (!this.props.loaded && this.props.loaded !== prevProps.loaded) {
      this.props.open(this.props.resourceSlug, this.props.embedded, this.loadApp)
    }
  }

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
      this.pending = null
    }

    if (!this.props.notFound) {
      this.props.close(this.props.resourceSlug, this.props.embedded)
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
                component: resolved.default.component,
                store: resolved.default.store,
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
    if (this.props.notFound) {
      return (
        <ContentNotFound
          size="lg"
          title={trans('not_found', {}, 'resource')}
          description={trans('not_found_desc', {}, 'resource')}
        >
          {!this.props.embedded &&
            <Button
              className="btn btn-emphasis"
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

    return (
      <ReactReduxContext.Consumer>
        {({ store }) => {
          // this will mount the requested reducer into the current redux store
          if (this.state.type && this.state.store) {
            store.injectReducer(this.state.type, this.state.store)
          }

          // just render the original component and forward its props
          if (!this.props.loaded) {
            return (
              <ContentLoader
                size="lg"
                description={trans('loading', {}, 'resource')}
              />
            )
          }

          return (
            <Fragment>
              {this.state.component && createElement(this.state.component, {
                path: this.props.path
              })}

              {0 !== this.state.styles.length &&
                <Helmet>
                  {this.state.styles.map(style =>
                    <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
                  )}
                </Helmet>
              }
            </Fragment>
          )
        }}
      </ReactReduxContext.Consumer>
    )
  }
}

ResourceMain.propTypes = {
  path: T.string.isRequired,
  resourceSlug: T.string.isRequired,
  resourceType: T.string,

  embedded: T.bool.isRequired,
  loaded: T.bool.isRequired,
  notFound: T.bool.isRequired,
  open: T.func.isRequired,
  close: T.func.isRequired
}

export {
  ResourceMain
}
