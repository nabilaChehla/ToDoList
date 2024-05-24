document.addEventListener('DOMContentLoaded', function() {
    const taskList = document.getElementById('task-list');

    fetch('../src/php/handle_form.php')
    .then(response => response.json())
    .then(data => {
        loadTasks(data);
    })
    .catch(error => console.error('Error:', error));

    function loadTasks(tasks) {
        taskList.innerHTML = '';
        tasks.forEach(task => {
            const li = document.createElement('li');
            li.className = task.state ? 'completed' : '';
            li.innerHTML = `
                <span>${task.description}</span>
                <span>(${task.created_at})</span>
            `;
            taskList.appendChild(li);
        });
    }
});

