# SmarText PDF

**SmarText PDF** is an intelligent PDF proofreading tool powered by **GPT-4o**. It extracts and analyzes text from PDF documents, detects grammatical and stylistic issues, and provides high-quality suggestions for improvement.

---

## ✨ Features

- 🔍 Extracts text from PDF documents
- 🤖 Uses GPT-4o for grammar, spelling, and tone correction
- 💡 Smart suggestions for improved phrasing and wording
- 📄 Paragraph-by-paragraph correction with side-by-side comparison
- 📤 Export options for cleaned text or revision summaries
- 🗂 Upload, compare, and manage your PDF files
- 🗣 Text-to-speech for revised content

---

## Requirements

- Python 3.8+
- Microsoft Word (for PDF to Word conversion)
- Windows OS (required for Word automation)
- OpenAI API Key (set as `API_KEY` in your `.env` file)
- The following Python packages (see `requirements.txt`):
  - `fastapi`
  - `uvicorn`
  - `python-docx`
  - `docx2pdf`
  - `pywin32`
  - `openai`
  - `python-dotenv`
  - `asyncio`

---

## 🚀 Getting Started

1. **Clone the repository**
   ```sh
   cd C:/xampp/htdocs
   git clone https://github.com/marksxiety/thesis-smarText-PDF.git
   ```

2. **Install Python dependencies**
   ```sh
   pip install -r requirements.txt
   ```

3. **Set your OpenAI API key**
   Create a `.env` file in your project directory with:
   ```
   API_KEY=sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```

4. **Start the API server**
   ```sh
   python grammar_checker.py
   ```
   The server will run at [http://localhost:5000](http://localhost:5000).

5. **Set up the database**
   - Open phpMyAdmin.
   - Create a new schema/database.
   - Import the `database.sql` file provided in the project.

6. **Start XAMPP and run Apache & MySQL**

7. **Access the web app**
   - Visit [http://localhost/SmarTextPDF_NewUI](http://localhost/SmarTextPDF_NewUI) in your browser.

---

## 📚 Usage

- **Upload PDFs**: Go to the Upload page to add new PDF files for proofreading.
- **Compare PDFs**: Use the Compare page to review original and revised text side-by-side, accept suggestions, and export results.
- **Dashboard**: View your recent uploads and proofreading statistics.

---

## 🛠 Troubleshooting

- Ensure Microsoft Word is installed and properly licensed on your system.
- The API server (`grammar_checker.py`) must be running for proofreading features to work.
- If you encounter issues with file uploads, check folder permissions for `/uploads` and `/processed_pdfs`.
- For OpenAI API errors, verify your API key in the `.env` file.

---

## 📄 License

This project is for educational and research purposes only.

---

## 🙏 Acknowledgements

- [OpenAI](https://openai.com/) for GPT-4o
- [pdf-lib](https://pdf-lib.js.org/) for PDF manipulation
- [FastAPI](https://fastapi.tiangolo.com/) for the backend API
- [XAMPP](https://www.apachefriends.org/) for local PHP/MySQL development

---

Enjoy using **SmarText PDF**!