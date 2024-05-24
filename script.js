document.addEventListener('DOMContentLoaded', function() {
    const taskForm = document.getElementById('taskForm');
    const taskInput = document.getElementById('taskInput');
    const taskList = document.getElementById('taskList');

    taskForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const taskDescription = taskInput.value.trim();
        if (taskDescription !== '') {
            addTask(taskDescription);
            taskInput.value = '';
        }
    });

    function addTask(description) {
        const listItem = document.createElement('li');
        listItem.className = 'taskItem';
        listItem.innerHTML = `
            <input type="checkbox">
            <span>${description}</span>
        `;
        taskList.appendChild(listItem);
    }
});
