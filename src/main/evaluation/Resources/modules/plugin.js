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
    'progression': () => { return import(/* webpackChunkName: "evaluation-tool-progression" */ '#/main/evaluation/tools/evaluation') }
  },

  data: {
    types: {
      'score': () => { return import(/* webpackChunkName: "app-data-type-score" */ '#/main/evaluation/data/types/score') },
      'sequence': () => { return import(/* webpackChunkName: "app-data-type-sequence" */ '#/main/evaluation/data/types/sequence') }
    },
    sources: {
      'resource_attempts'    : () => { return import(/* webpackChunkName: "evaluation-source-resource_attempts" */     '#/main/evaluation/data/sources/resource-attempts') },
      'resource_evaluations' : () => { return import(/* webpackChunkName: "evaluation-source-resource_evaluations" */  '#/main/evaluation/data/sources/resource-evaluations') },
      'my_resource_evaluations' : () => { return import(/* webpackChunkName: "evaluation-source-my_resource_evaluations" */  '#/main/evaluation/data/sources/my-resource-evaluations') },
      'sequences': () => { return import(/* webpackChunkName: "evaluation-source-sequences" */ '#/main/evaluation/data/sources/sequences') },
      'sequence_evaluations': () => { return import(/* webpackChunkName: "evaluation-source-sequence_evaluations" */ '#/main/evaluation/data/sources/sequence-evaluations') },
      'my_sequence_evaluations': () => { return import(/* webpackChunkName: "evaluation-source-my_sequence_evaluations" */ '#/main/evaluation/data/sources/my-sequence-evaluations') },
      'workspace_evaluations': () => { return import(/* webpackChunkName: "evaluation-source-workspace_evaluations" */ '#/main/evaluation/data/sources/workspace-evaluations') },
      'my_workspace_evaluations': () => { return import(/* webpackChunkName: "evaluation-source-my_workspace_evaluations" */ '#/main/evaluation/data/sources/my-workspace-evaluations') }
    }
  },

  actions: {
    progression: {
      'download-certificates': () => { return import(/* webpackChunkName: "evaluation-action-download-certificates" */ '#/main/evaluation/tools/evaluation/actions/download-certificates') },
      'regenerate-certificates': () => { return import(/* webpackChunkName: "evaluation-action-regenerate-certificates" */ '#/main/evaluation/tools/evaluation/actions/regenerate-certificates') },
      'init-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-init-evaluations" */ '#/main/evaluation/tools/evaluation/actions/init-evaluations') },
      'recompute-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-recompute-evaluations" */ '#/main/evaluation/tools/evaluation/actions/recompute-evaluations') },
      'purge-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-purge-evaluations" */ '#/main/evaluation/tools/evaluation/actions/purge-evaluations') },
      'export-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-export-evaluations" */ '#/main/evaluation/tools/evaluation/actions/export-evaluations') }
    },

    resource: {
      //'recompute-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-resource-recompute-evaluations" */   '#/main/evaluation/actions/resource/recompute-evaluations') },
      'purge-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-resource-purge-evaluations" */   '#/main/evaluation/actions/resource/purge-evaluations') },
      'export-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-resource-export-evaluations" */   '#/main/evaluation/actions/resource/export-evaluations') }
    },

    sequence: {
      'configure': () => { return import(/* webpackChunkName: "evaluation-action-sequence-configure" */ '#/main/evaluation/actions/sequence/configure') },
      'copy'     : () => { return import(/* webpackChunkName: "evaluation-action-sequence-copy" */      '#/main/evaluation/actions/sequence/copy') },
      'delete'   : () => { return import(/* webpackChunkName: "evaluation-action-sequence-delete" */    '#/main/evaluation/actions/sequence/delete') },
      'open'     : () => { return import(/* webpackChunkName: "evaluation-action-sequence-open" */      '#/main/evaluation/actions/sequence/open') },
      'publish'  : () => { return import(/* webpackChunkName: "evaluation-action-sequence-publish" */   '#/main/evaluation/actions/sequence/publish') },
      'unpublish': () => { return import(/* webpackChunkName: "evaluation-action-sequence-unpublish" */ '#/main/evaluation/actions/sequence/unpublish') },
      'show-dashboard': () => { return import(/* webpackChunkName: "evaluation-action-sequence-dashboard" */   '#/main/evaluation/actions/sequence/show-dashboard') },
      'download-certificates': () => { return import(/* webpackChunkName: "evaluation-action-sequence-download-certificates" */     '#/main/evaluation/actions/sequence/download-certificates') },
      'regenerate-certificates': () => { return import(/* webpackChunkName: "evaluation-action-sequence-regenerate-certificates" */ '#/main/evaluation/actions/sequence/regenerate-certificates') },
      'init-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-init-evaluations" */ '#/main/evaluation/actions/sequence/init-evaluations') },
      'recompute-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-recompute-evaluations" */   '#/main/evaluation/actions/sequence/recompute-evaluations') },
      'purge-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-purge-evaluations" */   '#/main/evaluation/actions/sequence/purge-evaluations') },
      'export-evaluations': () => { return import(/* webpackChunkName: "evaluation-action-export-evaluations" */   '#/main/evaluation/actions/sequence/export-evaluations') }
    },

    resource_attempt: {
      'open': () => { return import(/* webpackChunkName: "evaluation-action-resource_evaluation-open" */ '#/main/evaluation/actions/resource_attempt/open') },
      'open-resource': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-open-resource" */ '#/main/evaluation/actions/resource_attempt/open-resource') },
      'send-message': () => { return import(/* webpackChunkName: "evaluation-action-resource_evaluation-send-message" */ '#/main/evaluation/actions/resource_attempt/send-message') },
      'show-profile': () => { return import(/* webpackChunkName: "evaluation-action-resource_evaluation-show-profile" */ '#/main/evaluation/actions/resource_attempt/show-profile') },
      'delete': () => { return import(/* webpackChunkName: "evaluation-action-resource_evaluation-delete" */ '#/main/evaluation/actions/resource_attempt/delete') },
    },

    resource_evaluation: {
      'open': () => { return import(/* webpackChunkName: "evaluation-action-resource_evaluation-open" */ '#/main/evaluation/actions/resource_evaluation/open') },
      'open-resource': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-open-resource" */ '#/main/evaluation/actions/resource_evaluation/open-resource') },
      'send-message': () => { return import(/* webpackChunkName: "evaluation-action-resource_evaluation-send-message" */ '#/main/evaluation/actions/resource_evaluation/send-message') },
      'show-profile': () => { return import(/* webpackChunkName: "evaluation-action-resource_evaluation-show-profile" */ '#/main/evaluation/actions/resource_evaluation/show-profile') },
      'delete': () => { return import(/* webpackChunkName: "evaluation-action-resource_evaluation-delete" */ '#/main/evaluation/actions/resource_evaluation/delete') },
      'give-attempt': () => { return import(/* webpackChunkName: "evaluation-action-resource_evaluation-give-attempt" */ '#/main/evaluation/actions/resource_evaluation/give-attempt') }
    },

    sequence_evaluation: {
      'open': () => { return import(/* webpackChunkName: "evaluation-action-sequence_evaluation-open" */ '#/main/evaluation/actions/sequence_evaluation/open') },
      'open-sequence': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-open-sequence" */ '#/main/evaluation/actions/sequence_evaluation/open-sequence') },
      'send-message': () => { return import(/* webpackChunkName: "evaluation-action-sequence_evaluation-send-message" */ '#/main/evaluation/actions/sequence_evaluation/send-message') },
      'show-profile': () => { return import(/* webpackChunkName: "evaluation-action-sequence_evaluation-show-profile" */ '#/main/evaluation/actions/sequence_evaluation/show-profile') },
      'download-certificate': () => { return import(/* webpackChunkName: "evaluation-action-sequence_evaluation-certificate" */ '#/main/evaluation/actions/sequence_evaluation/download-certificate') },
      'regenerate-certificate': () => { return import(/* webpackChunkName: "evaluation-action-sequence_evaluation-regenerate-certificate" */ '#/main/evaluation/actions/sequence_evaluation/regenerate-certificate') },
      'delete': () => { return import(/* webpackChunkName: "evaluation-action-sequence_evaluation-delete" */ '#/main/evaluation/actions/sequence_evaluation/delete') },
      'recompute': () => { return import(/* webpackChunkName: "evaluation-action-sequence_evaluation-recompute" */ '#/main/evaluation/actions/sequence_evaluation/recompute') }
    },

    workspace_evaluation: {
      'open': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-open" */ '#/main/evaluation/actions/workspace_evaluation/open') },
      'open-workspace': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-open-ws" */ '#/main/evaluation/actions/workspace_evaluation/open-workspace') },
      'send-message': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-send-message" */ '#/main/evaluation/actions/workspace_evaluation/send-message') },
      'show-profile': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-show-profile" */ '#/main/evaluation/actions/workspace_evaluation/show-profile') },
      'download-certificate': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-certificate" */ '#/main/evaluation/actions/workspace_evaluation/download-certificate') },
      'regenerate-certificate': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-regenerate-certificate" */ '#/main/evaluation/actions/workspace_evaluation/regenerate-certificate') },
      'recompute': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-recompute" */ '#/main/evaluation/actions/workspace_evaluation/recompute') },
      'delete': () => { return import(/* webpackChunkName: "evaluation-action-workspace_evaluation-delete" */ '#/main/evaluation/actions/workspace_evaluation/delete') }
    }
  },

  badge_rules: {
    'resource_progression': () => { return import(/* webpackChunkName: "evaluation-badge-resource_progression" */ '#/main/evaluation//badge_rules/resource_progression') },
    'resource_score': () => { return import(/* webpackChunkName: "evaluation-badge-resource_score" */ '#/main/evaluation//badge_rules/resource_score') },
    'resource_status': () => { return import(/* webpackChunkName: "evaluation-badge-resource_status" */ '#/main/evaluation//badge_rules/resource_status') },
    'sequence_progression': () => { return import(/* webpackChunkName: "evaluation-badge-sequence_progression" */ '#/main/evaluation//badge_rules/sequence_progression') },
    'sequence_score': () => { return import(/* webpackChunkName: "evaluation-badge-sequence_score" */ '#/main/evaluation//badge_rules/sequence_score') },
    'sequence_status': () => { return import(/* webpackChunkName: "evaluation-badge-sequence_status" */ '#/main/evaluation//badge_rules/sequence_status') },
    'workspace_progression': () => { return import(/* webpackChunkName: "evaluation-badge-workspace_progression" */ '#/main/evaluation//badge_rules/workspace_progression') },
    'workspace_score': () => { return import(/* webpackChunkName: "evaluation-badge-workspace_score" */ '#/main/evaluation//badge_rules/workspace_score') },
    'workspace_status': () => { return import(/* webpackChunkName: "evaluation-badge-workspace_status" */ '#/main/evaluation//badge_rules/workspace_status') }
  }
})
