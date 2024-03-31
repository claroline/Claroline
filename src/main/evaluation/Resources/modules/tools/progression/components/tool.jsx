import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Alert} from '#/main/app/components/alert'
import {Tool} from '#/main/core/tool'
import {ToolPage} from '#/main/core/tool/containers/page'

import {WorkspaceEvaluation} from '#/main/evaluation/workspace/components/evaluation'
import {ContentLoader} from '#/main/app/content/components/loader'

const ProgressionTool = (props) =>
  <Tool
    {...props}
    pages={[
      {
        path: '/',
        render: () => (
          <ToolPage>
            {!props.loaded &&
              <ContentLoader
                className="row"
                size="lg"
                description="Nous chargeons la progression..."
              />
            }

            {props.loaded && !props.workspaceEvaluation &&
              <Alert type="warning">
                Vous n'avez pas de progression pour cet espace.
              </Alert>
            }
            {props.loaded && props.workspaceEvaluation &&
              <WorkspaceEvaluation
                workspaceEvaluation={props.workspaceEvaluation}
                resourceEvaluations={props.resourceEvaluations}
              />
            }
          </ToolPage>
        )
      }
    ]}
  />

ProgressionTool.propTypes = {
  loaded: T.bool.isRequired,
  workspaceEvaluation: T.object,
  resourceEvaluations: T.array
}

export {
  ProgressionTool
}
