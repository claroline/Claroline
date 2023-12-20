/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Evaluation plugin.
 */
registry.add('ClarolineEvaluationBundle', {
  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'evaluation': () => { return import(/* webpackChunkName: "evaluation-tool-evaluation" */ '#/main/evaluation/tools/evaluation') }
  },

  data: {
    sources: {
      'resource_attempts'    : () => { return import(/* webpackChunkName: "evaluation-source-resource_attempts" */     '#/main/evaluation/data/sources/resource-attempts') },
      'resource_evaluations' : () => { return import(/* webpackChunkName: "evaluation-source-resource_evaluations" */  '#/main/evaluation/data/sources/resource-evaluations') },
      'my_resource_evaluations' : () => { return import(/* webpackChunkName: "evaluation-source-my_resource_evaluations" */  '#/main/evaluation/data/sources/my-resource-evaluations') },
      'workspace_evaluations': () => { return import(/* webpackChunkName: "evaluation-source-workspace_evaluations" */ '#/main/evaluation/data/sources/workspace-evaluations') },
      'my_workspace_evaluations': () => { return import(/* webpackChunkName: "evaluation-source-my_workspace_evaluations" */ '#/main/evaluation/data/sources/my-workspace-evaluations') }
    }
  },

  actions: {
    resource: {
      'evaluation': () => { return import(/* webpackChunkName: "evaluation-action-resource-evaluation" */ '#/main/evaluation/actions/resource/evaluation') }
    },

    workspace_evaluation: {
      'open': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-open" */ '#/main/evaluation/actions/workspace_evaluation/open') },
      'open-workspace': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-open-ws" */ '#/main/evaluation/actions/workspace_evaluation/open-workspace') },
      'send-message': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-send-message" */ '#/main/evaluation/actions/workspace_evaluation/send-message') },
      'show-profile': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-show-profile" */ '#/main/evaluation/actions/workspace_evaluation/show-profile') },
      'download-participation-certificate': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-p-certificate" */ '#/main/evaluation/actions/workspace_evaluation/download-participation-certificate') },
      'download-success-certificate': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-s-certificate" */ '#/main/evaluation/actions/workspace_evaluation/download-success-certificate') }
    }
  }
})
