import os
import re
import sys

# Add parent dir to path to import create_docx
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import create_docx

create_docx.markdown_to_docx("helpdesk_sugerencias.md", "helpdesk_sugerencias.docx")
