import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {selectors} from '#/main/transfer/tools/transfer/log/store'

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

const LogsComponent = props =>
  <Fragment>
    <ContentInfoBlocks
      className="my-4"
      size="lg"
      items={[
        {
          icon: 'fa fa-list',
          label: trans('total'),
          value: !isEmpty(props.data) ? get(props.data, 'total') || '0' : '?'
        }, {
          icon: 'fa fa-sync',
          label: trans('processed'),
          value: !isEmpty(props.data) ? get(props.data, 'processed') || '0' : '?'
        }, {
          icon: 'fa fa-check',
          label: trans('success'),
          value: !isEmpty(props.data) ? get(props.data, 'success') || '0' : '?'
        }, {
          icon: 'fa fa-exclamation-triangle',
          label: trans('error'),
          value: !isEmpty(props.data) ? get(props.data, 'error') || '0' : '?'
        }
      ]}
    />

    {isEmpty(props.data) &&
      <ContentPlaceholder
        icon="fa fa-clipboard-list"
        title={trans('no_log', {}, 'transfer')}
        help={trans('no_log_help', {}, 'transfer')}
      />
    }

    {!isEmpty(props.data) &&
      <FormSections
        level={3}
        defaultOpened="log-section"
      >
        <FormSection
          id="log-section"
          className="embedded-list-section"
          title={trans('log')}
        >
          <pre>{props.data.log}</pre>
        </FormSection>

        <FormSection
          id="success-section"
          className="embedded-list-section"
          title={trans('success')}
        >
          <Fragment>
            {props.data.data && Object.keys(props.data.data.success).map((action, i) =>
              <div key={'success' + i}>
                <h4>{trans(action, {}, 'transfer')}</h4>
                {props.data.data.success[action].map((success, j) =>
                  <Success key={'success' + i + j} {...success} />
                )}
              </div>
            )}
          </Fragment>
        </FormSection>

        <FormSection
          id="error-section"
          className="embedded-list-section"
          title={trans('error')}
        >
          <Fragment>
            {props.data.data && props.data.data.error.map((error, k) =>
              <Error key={'error' + k} {...error}/>
            )}
          </Fragment>
        </FormSection>
      </FormSections>
    }
  </Fragment>

LogsComponent.propTypes = {
  data: T.object.isRequired
}

const Logs = connect(
  state => ({
    data: selectors.log(state)
  })
)(LogsComponent)

export {
  Logs
}
