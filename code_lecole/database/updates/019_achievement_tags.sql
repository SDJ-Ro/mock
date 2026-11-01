-- The honours board tags a result with the sport itself ("Football", "Athletics"), not just "Sports".
UPDATE achievements SET category = 'Football'  WHERE title = 'Div-1 League Runners Up' AND category = 'Sports';
UPDATE achievements SET category = 'Athletics' WHERE title = '400m All-Island Gold' AND category = 'Sports';
