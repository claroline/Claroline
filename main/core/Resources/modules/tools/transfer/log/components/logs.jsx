import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {selectors} from '#/main/core/tools/transfer/store'

const Error = error => {
  return(
    <pre>
      <div>{trans('line')}: {error.line}</div>
      {typeof error.value === 'string' ?
        error.value:
        Object.keys(error.value).map((key, i) => <div key={'error'+key+i}>{error.value[key].path}: {error.value[key].message}</div>)
      }
    </pre>
  )
}

Error.propTypes = {
}

const Success = success => {
  return(
    <pre>
      {success.log}
    </pre>
  )
}

Success.propTypes = {
}

const Logs = props => {
  if (props.data) {
    return (
      <div>
        <pre>
          {trans('processed')}: {props.data.processed} {'\n'}
          {trans('error')}: {props.data.error} {'\n'}
          {trans('success')}: {props.data.success} {'\n'}
          {trans('total')}: {props.data.total} {'\n'}
        </pre>

        <FormSections
          level={3}
          defaultOpened="log-section"
        >
          <FormSection
            id="log-section"
            className="embedded-list-section"
            title={trans('log')}
          >
            <pre>
              {props.data.log}
            </pre>
          </FormSection>
          <FormSection
            id="success-section"
            className="embedded-list-section"
            title={trans('success')}
          >
            <div>
              {props.data.data &&
                Object.keys(props.data.data.success).map((action, i) => {
                  return(
                    <div key={'success'+i}>
                      <h4>{action}</h4>
                      {props.data.data.success[action].map((success, j) =>  <Success key={'success'+i+j} {...success}/>)}
                    </div>
                  )}
                )
              }
            </div>
          </FormSection>
          <FormSection
            id="error-section"
            className="embedded-list-section"
            title={trans('error')}
          >
            <div>
              {props.data.data && props.data.data.error.map((error, k) =>
                <Error key={'error'+k} {...error}/>
              )}
            </div>
          </FormSection>
        </FormSections>
      </div>
    )
  } else {
    return(<div> Loading... </div>)
  }
}

Logs.propTypes = {
  data: T.object.isRequired
}

const ConnectedLog = connect(
  state => ({
    data: selectors.log(state)
  }),
  null
)(Logs)

export {
  ConnectedLog as Logs
}
