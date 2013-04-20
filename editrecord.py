import re
import sys
import string
from pymarc import Record, Field, MARCReader, MARCWriter

#well, since the API still isn't out yet...
does_the_API_exist_yet = "no"
if does_the_API_exist_yet == "no":
	sys.exit("Sorry, still waiting on that API...")

#define variables from command line entries from index.php
word = sys.argv[1]
wrong_word = sys.argv[2]
search_type = sys.argv[3]

if search_type == 'full':
	wrong_word_rx =  '(^|\s)' + wrong_word + '\\s'
elif search_type == 'partial':
	wrong_word_rx = '(^|\s)' + wrong_word + '[\w]'
else:
	sys.exit("Sorry, missing search type var")

###define functions ###
def subfield_to_string(sfv):
	'''takes the subfield value (sfv) and does the following:
		- convert to string
		- strip out brackets'''
	sfv = str(sfv)
	sfv = sfv.rstrip('\']')
	sfv = sfv.lstrip('[\'')
	return sfv
	
def fix_245_misspelling(sfv,word,subfield,title):
	'''fixes misspelling in 245 field. 
		Recreate 245 field and delete old one after fix'''
	if re.search(wrong_word_rx, sfv):
		title_fix = sfv.replace(wrong_word, word)
		for field in title:
			marc['245'][subfield] = title_fix
	else:
		pass

### end functions ###


records = MARCReader(open(sys.argv[4]))

for marc in records:	
	
	#get the subfield a, b of the 245 field
	title_245 = marc.get_fields('245')
	for field in title_245:
		title_a_raw = field.get_subfields('a')
		title_b_raw = field.get_subfields('b')
	
	title_a_raw = subfield_to_string(title_a_raw)
	title_b_raw = subfield_to_string(title_b_raw)

	#check to see if an RDA record that has the 246 $i note and has the misspelling in 245 
	#if so, move on to the next record
	#thanks to @zemkat for the whack to the head w/a clue bat about this one
	title_246 = marc.get_fields('246')
	rda = False
	for field in title_246:
		rda_246 = field.get_subfields('i')
		rda_246 = str(rda_246)
		rda_246 = rda_246.lower()
		if rda_246.find('title should read') and not (re.search(wrong_word_rx, title_a_raw) and re.search(wrong_word_rx, title_b_raw)):
			rda = True
		else:
			rda = False
	if rda:
		continue #skip record, go onto next record

	#time to fix that pesky misspelling...
	fix_245_misspelling(title_a_raw,word,'a',title_245)
	fix_245_misspelling(title_b_raw,word,'b',title_245)
	
	#get the bib record from the 907 field prior to deletion
	n = marc.get_fields('907')
	for field in n:
		bib_rec_num_raw = field.get_subfields('a')

	bib_rec_num = subfield_to_string(bib_rec_num_raw)

	#add 949 local field for overlay of bib record and creation of order record when record is uploaded into Millennium
	marc.add_field(
		Field(
			tag = '949',
			indicators = [' ',' '],
			subfields = [
				'a', '*recs-b;ov-%s;' %(bib_rec_num)
			]))	

	#delete 907, 998, 910, 945 fields
	for f in marc.get_fields('907', '998', '910', '945'):
		  if f['a'] != '':
			  marc.remove_field(f) 
	
	#append record to a generic file.dat file
	writer = MARCWriter(file(word+'.dat','a'))
	writer.write(marc)

#closes .dat file
writer.close() 