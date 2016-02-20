<?php

namespace Kanboard\Controller;

/**
 * Column controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
class Column extends Base
{
    /**
     * Display columns list
     *
     * @access public
     */
    public function index()
    {
        $project = $this->getProject();
        $columns = $this->board->getColumns($project['id']);

        $this->response->html($this->helper->layout->project('column/index', array(
            'columns' => $columns,
            'project' => $project,
            'title' => t('Edit board')
        )));
    }

    /**
     * Show form to create a new column
     *
     * @access public
     */
    public function create(array $values = array(), array $errors = array())
    {
        $project = $this->getProject();
        $columns = $this->board->getColumnsList($project['id']);

        if (empty($values)) {
            $values = array('project_id' => $project['id']);
        }

        $this->response->html($this->template->render('column/create', array(
            'values' => $values,
            'errors' => $errors,
            'project' => $project,
            'title' => t('Add a new column')
        )));
    }

    /**
     * Validate and add a new column
     *
     * @access public
     */
    public function save()
    {
        $project = $this->getProject();
        $values = $this->request->getValues();

        list($valid, $errors) = $this->columnValidator->validateCreation($values);

        if ($valid) {
            if ($this->board->addColumn($project['id'], $values['title'], $values['task_limit'], $values['description'])) {
                $this->flash->success(t('Column created successfully.'));
                return $this->response->redirect($this->helper->url->to('column', 'index', array('project_id' => $project['id'])), true);
            } else {
                $errors['title'] = array(t('Another column with the same title exists in the project'));
            }
        }

        $this->create($values, $errors);
    }

    /**
     * Display a form to edit a column
     *
     * @access public
     */
    public function edit(array $values = array(), array $errors = array())
    {
        $project = $this->getProject();
        $column = $this->board->getColumn($this->request->getIntegerParam('column_id'));

        $this->response->html($this->helper->layout->project('column/edit', array(
            'errors' => $errors,
            'values' => $values ?: $column,
            'project' => $project,
            'column' => $column,
            'title' => t('Edit column "%s"', $column['title'])
        )));
    }

    /**
     * Validate and update a column
     *
     * @access public
     */
    public function update()
    {
        $project = $this->getProject();
        $values = $this->request->getValues();

        list($valid, $errors) = $this->columnValidator->validateModification($values);

        if ($valid) {
            if ($this->board->updateColumn($values['id'], $values['title'], $values['task_limit'], $values['description'])) {
                $this->flash->success(t('Board updated successfully.'));
                $this->response->redirect($this->helper->url->to('column', 'index', array('project_id' => $project['id'])));
            } else {
                $this->flash->failure(t('Unable to update this board.'));
            }
        }

        $this->edit($values, $errors);
    }

    /**
     * Move a column up or down
     *
     * @access public
     */
    public function move()
    {
        $this->checkCSRFParam();
        $project = $this->getProject();
        $column_id = $this->request->getIntegerParam('column_id');
        $direction = $this->request->getStringParam('direction');

        if ($direction === 'up' || $direction === 'down') {
            $this->board->{'move'.$direction}($project['id'], $column_id);
        }

        $this->response->redirect($this->helper->url->to('column', 'index', array('project_id' => $project['id'])));
    }

    /**
     * Confirm column suppression
     *
     * @access public
     */
    public function confirm()
    {
        $project = $this->getProject();

        $this->response->html($this->helper->layout->project('column/remove', array(
            'column' => $this->board->getColumn($this->request->getIntegerParam('column_id')),
            'project' => $project,
            'title' => t('Remove a column from a board')
        )));
    }

    /**
     * Remove a column
     *
     * @access public
     */
    public function remove()
    {
        $project = $this->getProject();
        $this->checkCSRFParam();
        $column = $this->board->getColumn($this->request->getIntegerParam('column_id'));

        if (! empty($column) && $this->board->removeColumn($column['id'])) {
            $this->flash->success(t('Column removed successfully.'));
        } else {
            $this->flash->failure(t('Unable to remove this column.'));
        }

        $this->response->redirect($this->helper->url->to('column', 'index', array('project_id' => $project['id'])));
    }
}
