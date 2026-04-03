const checkboxes = document.querySelectorAll('input[type="checkbox"]');
const cards = document.querySelectorAll(".card");

checkboxes.forEach(cb => cb.addEventListener('change', filterCards));

function filterCards(){
    const checked = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value.toLowerCase());
    cards.forEach(card => {
        if (checked.length === 0){
            card.style.display = 'block';
            return;
        }
        const tags = Array.from(card.querySelectorAll('.tags span')).map(span => span.textContent.toLowerCase());
        const match = checked.some(filter => tags.includes(filter));
        card.style.display = match ?  'block' : 'none';
    });
}