import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {selectors} from '#/main/core/tools/transfer/store'

const Error = props =>
  <pre>
    <div>{trans('line')}: {props.line}</div>

    {typeof props.value === 'string' ?
      props.value :
      Object.keys(props.value).map((key, i) => <div key={'error'+key+i}>{props.value[key].path}: {props.value[key].message}</div>)
    }
  </pre>

Error.propTypes = {
  line: T.number,
  value: T.oneOfType([T.string, T.arrayOf(T.shape({
    path: T.string,
    message: T.string
  }))])
}

const Success = props =>
  <pre>
    {props.log}
  </pre>

Success.propTypes = {
  log: T.string
}

const Logs = props => {
  if (!isEmpty(props.data)) {
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
  }

  return null
}

Logs.propTypes = {
  data: T.object.isRequired
}

const ConnectedLog = connect(
  state => ({
    data: selectors.log(state)
  })
)(Logs)

export {
  ConnectedLog as Logs
}
