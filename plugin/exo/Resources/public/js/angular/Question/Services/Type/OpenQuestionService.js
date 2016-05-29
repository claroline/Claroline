/**
 * Open Question Service
 * @param {FeedbackService}  FeedbackService
 * @constructor
 */
var OpenQuestionService = function OpenQuestionService(FeedbackService) {
    AbstractQuestionService.apply(this, arguments);
    
    this.FeedbackService = FeedbackService;
};

// Extends AbstractQuestionCtrl
OpenQuestionService.prototype = Object.create(AbstractQuestionService.prototype);

// Set up dependency injection (get DI from parent too)
OpenQuestionService.$inject = AbstractQuestionService.$inject.concat(['FeedbackService']);

/**
 * Initialize the answer object for the Question
 */
OpenQuestionService.prototype.initAnswer = function initAnswer() {
    return '';
};

/**
 * 
 * @returns {answersAllFound}
 */
OpenQuestionService.prototype.answersAllFound = function answersAllFound(question, answer) {
    var numAnswersFound = 0;
    var answerWithKeywords = answer ? answer : '';

    // Get EOL
    answerWithKeywords = answerWithKeywords.replace(/(\r\n|\n|\r)/gm, '<br/>');

    if ('long' !== question.typeOpen) {
        // Initialize answer with keywords
        // Search used keywords in student answer
        for (var i = 0; i < question.solutions.length; i++) {
            var solution = question.solutions[i];

            // Check in answer if the keyword as been used
            var searchFlags      = 'g' + (solution.caseSensitive ? 'i' : '');
            var searchExpression = new RegExp(solution.word, searchFlags);
            if (-1 !== answer.search(searchExpression)) {
                numAnswersFound++;
            }
        }
    } else {
        feedbackState = this.FeedbackService.SOLUTION_FOUND;
    }
    
    var feedbackState = -1;
    if (question.solutions.length === numAnswersFound) {
        feedbackState = this.FeedbackService.SOLUTION_FOUND;
    } else if (question.solutions.length -1 === numAnswersFound) {
        feedbackState = this.FeedbackService.ONE_ANSWER_MISSING;
    } else {
        feedbackState = this.FeedbackService.MULTIPLE_ANSWERS_MISSING;
    }
    
    return feedbackState;
};

/**
 * Get the correct answer from the solutions of a Question
 * For type = long we can not generate a correct answer at it requires a manual correction
 * @param   {Object} question
 * @returns {Object}
 */
OpenQuestionService.prototype.getCorrectAnswer = function getCorrectAnswer(question) {
    var answer = null;

    if (question.solutions && 'long' !== question.typeOpen) {
        answer = [];

        // Only get the list of required keywords
        if ('oneWord' === question.typeOpen) {
            // One word answer (get the keyword with the max score)
            var betterFound = null;
            for (var i = 0; i < question.solutions.length; i++) {
                if (null === betterFound || question.solutions[i].score > betterFound.score) {
                    betterFound = question.solutions[i];
                }
            }

            answer.push(betterFound);
        } else if ('short' === question.typeOpen) {
            // Short answer (display all keywords with a positive score as expected answer)
            answer = question.solutions;
        }
    }

    return answer;
};

// Register service into AngularJS
angular
    .module('Question')
    .service('OpenQuestionService', OpenQuestionService);
